import React, { useEffect, useRef, useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  Alert,
  Animated,
  StatusBar,
} from 'react-native';
import {
  RTCView,
  mediaDevices,
  RTCPeerConnection,
  RTCIceCandidate,
  RTCSessionDescription,
  MediaStreamTrack,
} from 'react-native-webrtc';
import { useTheme } from '../../../context/ThemeContext';

interface LiveAdminProps {
  adminId: string;
  onStopLive: () => void;
}

export default function LiveStream({ adminId, onStopLive }: LiveAdminProps) {
  const { colors } = useTheme();
  const [localStream, setLocalStream] = useState<any>(null);
  const localStreamRef = useRef<any>(null);
  const [viewerCount, setViewerCount] = useState(0);
  const [isLive, setIsLive] = useState(false);
  const [streamDuration, setStreamDuration] = useState(0);

  const peerConnections = useRef<Map<string, RTCPeerConnection>>(new Map());
  const signalingServer = useRef<WebSocket | null>(null);
  const pulseAnim = useRef(new Animated.Value(1)).current;
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const streamStartTime = useRef<Date | null>(null);
  const durationInterval = useRef<NodeJS.Timeout | null>(null);

  useEffect(() => {
    if (isLive) {
      const pulse = () => {
        Animated.sequence([
          Animated.timing(pulseAnim, {
            toValue: 1.2,
            duration: 1000,
            useNativeDriver: true,
          }),
          Animated.timing(pulseAnim, {
            toValue: 1,
            duration: 1000,
            useNativeDriver: true,
          }),
        ]).start(() => pulse());
      };
      pulse();
    }
  }, [isLive, pulseAnim]);

  useEffect(() => {
    Animated.timing(fadeAnim, {
      toValue: 1,
      duration: 800,
      useNativeDriver: true,
    }).start();
  }, [fadeAnim]);

  useEffect(() => {
    if (isLive && streamStartTime.current) {
      durationInterval.current = setInterval(() => {
        const now = new Date();
        const diff = Math.floor(
          (now.getTime() - streamStartTime.current!.getTime()) / 1000,
        );
        setStreamDuration(diff);
      }, 1000);
    }

    return () => {
      if (durationInterval.current) {
        clearInterval(durationInterval.current);
      }
    };
  }, [isLive]);

  const formatDuration = (seconds: number) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    return `${hours.toString().padStart(2, '0')}:${minutes
      .toString()
      .padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
  };

  function debugLog(message: string) {
    const time = new Date().toLocaleTimeString();
    console.log(`[${time}] ${message}`);
  }

  useEffect(() => {
    debugLog('🚀 Démarrage du streamer');

    mediaDevices
      .getUserMedia({
        video: {
          width: { ideal: 1920 },
          height: { ideal: 1080 },
          frameRate: { ideal: 30 },
        },
        audio: {
          echoCancellation: true,
          noiseSuppression: true,
        },
      })
      .then(stream => {
        setLocalStream(stream);
        localStreamRef.current = stream;
        debugLog(`✅ Stream local obtenu: ${stream.getTracks().length} tracks`);
        stream.getTracks().forEach((track: any) => {
          debugLog(`📹 Track disponible: ${track.kind} - ${track.label}`);
        });

        signalingServer.current = new WebSocket('ws://192.168.88.29:9090');

        signalingServer.current.onopen = () => {
          debugLog('✅ Connexion WebSocket établie (streamer)');
          setIsLive(true);
          streamStartTime.current = new Date();

          setTimeout(() => {
            if (
              signalingServer.current &&
              signalingServer.current.readyState === WebSocket.OPEN
            ) {
              const msg = { type: 'streamer', adminId };
              signalingServer.current.send(JSON.stringify(msg));
              debugLog(`📤 Message streamer envoyé: ${JSON.stringify(msg)}`);
            } else {
              console.warn('WebSocket non prêt après délai');
            }
          }, 100);
        };

        signalingServer.current.onerror = event => {
          debugLog(`❌ Erreur WebSocket: ${JSON.stringify(event)}`);
        };

        signalingServer.current.onclose = event => {
          debugLog(
            `🔌 Connexion WebSocket fermée: ${event.code} - ${event.reason}`,
          );
          setIsLive(false);
        };

        signalingServer.current.onmessage = async event => {
          const data = JSON.parse(event.data);
          debugLog(`📥 Message reçu: ${JSON.stringify(data)}`);

          if (data.type === 'newViewer') {
            const viewerId = data.viewerId;
            debugLog(`🆕 Nouveau viewer: ${viewerId}`);

            debugLog('Vérification du stream local...');
            if (!localStreamRef.current) {
              debugLog('⚠️ Pas de stream local disponible');
              return;
            }
            debugLog('Stream local disponible, ajout des tracks...');

            const pc = new RTCPeerConnection({
              iceServers: [{ urls: 'stun:stun.l.google.com:19302' }],
            });

            localStreamRef.current
              .getTracks()
              .forEach((track: MediaStreamTrack) => {
                pc.addTrack(track, localStreamRef.current);
                debugLog(
                  `🎬 Track ${track.kind} ajouté pour viewer ${viewerId}`,
                );
              });

            pc.onicecandidate = event => {
              if (event.candidate) {
                debugLog(
                  `🧊 ICE candidate pour viewer ${viewerId}: ${event.candidate.candidate}`,
                );
                signalingServer.current?.send(
                  JSON.stringify({
                    type: 'candidate',
                    candidate: event.candidate,
                    target: 'viewer',
                    viewerId,
                  }),
                );
              } else {
                debugLog(
                  `🧊 Tous ICE candidates envoyés pour viewer ${viewerId}`,
                );
              }
            };

            pc.onconnectionstatechange = () => {
              debugLog(
                `🔗 Connection state pour ${viewerId}: ${pc.connectionState}`,
              );
            };

            try {
              debugLog(`📝 Création offer pour viewer ${viewerId}...`);
              const offer = await pc.createOffer();
              await pc.setLocalDescription(offer);
              debugLog(`✅ Offer créée pour viewer ${viewerId}`);

              const offerMessage = {
                type: 'offer',
                offer,
                viewerId,
              };

              debugLog(`📤 Envoi offer: ${JSON.stringify(offerMessage)}`);
              signalingServer.current?.send(JSON.stringify(offerMessage));

              peerConnections.current.set(viewerId, pc);
              debugLog(`💾 PeerConnection stockée pour viewer ${viewerId}`);
            } catch (error) {
              debugLog(
                `❌ Erreur création offer pour ${viewerId}: ${error.message}`,
              );
            }
          }
          else if (data.type === 'answer') {
            const pc = peerConnections.current.get(data.viewerId);
            if (pc) {
              await pc.setRemoteDescription(new RTCSessionDescription(data.answer));
              debugLog(`✅ Answer appliquée pour viewer ${data.viewerId}`);
            }
          }

          else if (data.type === 'candidate') {
            const pc = peerConnections.current.get(data.viewerId);
            if (pc && data.candidate) {
              try {
                await pc.addIceCandidate(new RTCIceCandidate(data.candidate));
                debugLog(`✅ ICE candidate ajoutée pour viewer ${data.viewerId}`);
              } catch (e) {
                debugLog(`❌ Erreur ajout ICE candidate: ${e.message}`);
              }
            }
          }
        };
      })
      .catch(err => {
        debugLog(`❌ Erreur accès caméra: ${err.message}`);
        Alert.alert(
          'Erreur',
          "Impossible d'accéder à la caméra/micro : " + err.message,
        );
      });

    return () => {
      debugLog('🛑 Début du cleanup...');

      peerConnections.current.forEach((pc, viewerId) => {
        pc.close();
        debugLog(`🔒 PeerConnection fermée pour viewer ${viewerId}`);
      });
      // eslint-disable-next-line react-hooks/exhaustive-deps
      peerConnections.current.clear();

      if (signalingServer.current) {
        signalingServer.current.close();
        signalingServer.current = null;
        debugLog('🔌 WebSocket fermé');
      }

      if (localStream) {
        localStream.getTracks().forEach((track: any) => {
          track.stop();
          debugLog(`🛑 Track ${track.kind} arrêté`);
        });
        setLocalStream(null);
      }

      if (durationInterval.current) {
        clearInterval(durationInterval.current);
      }

      debugLog('✅ Cleanup terminé');
    };
  }, [adminId]);

  const handleStopLive = () => {
    Alert.alert(
      'Arrêter le live',
      'Êtes-vous sûr de vouloir terminer la diffusion ?',
      [
        { text: 'Annuler', style: 'cancel' },
        {
          text: 'Terminer',
          style: 'destructive',
          onPress: () => {
            debugLog("🛑 Arrêt du live demandé par l'utilisateur");
            setIsLive(false);
            onStopLive();
          },
        },
      ],
    );
  };

  return (
    <View style={[styles.container, { backgroundColor: colors.background }]}>
      <StatusBar barStyle="dark-content" backgroundColor={colors.background} />

      <Animated.View style={[styles.content, { opacity: fadeAnim }]}>
        <View style={[styles.header, { backgroundColor: colors.surface }]}>
          <View style={styles.leftSection}>
            <View style={styles.liveIndicatorContainer}>
              <Animated.View
                style={[
                  styles.liveIndicator,
                  {
                    transform: [{ scale: pulseAnim }],
                    backgroundColor: isLive ? colors.error : colors.placeholder,
                  },
                ]}
              />
              <Text
                style={[
                  styles.liveText,
                  { color: isLive ? colors.error : colors.placeholder },
                ]}
              >
                {isLive ? 'LIVE' : 'OFFLINE'}
              </Text>
            </View>

            {isLive && (
              <Text style={[styles.durationText, { color: colors.text }]}>
                {formatDuration(streamDuration)}
              </Text>
            )}
          </View>

          <View style={styles.rightSection}>
            <View
              style={[styles.viewersBadge, { backgroundColor: colors.primary }]}
            >
              <Text style={styles.viewersIcon}>👁️</Text>
              <Text style={[styles.viewersCount, { color: colors.background }]}>
                {viewerCount}
              </Text>
            </View>

            <TouchableOpacity
              style={[styles.adminBadge, { backgroundColor: colors.text }]}
              activeOpacity={0.8}
            >
              <Text style={[styles.adminText, { color: colors.background }]}>
                👑 {adminId}
              </Text>
            </TouchableOpacity>
          </View>
        </View>

        {/* Vidéo en plein écran */}
        <View style={[styles.videoContainer, { backgroundColor: colors.text }]}>
          {localStream ? (
            <RTCView
              streamURL={localStream.toURL()}
              style={styles.video}
              objectFit="cover"
              mirror={true}
            />
          ) : (
            <View
              style={[
                styles.videoPlaceholder,
                { backgroundColor: colors.surface },
              ]}
            >
              <View
                style={[
                  styles.placeholderIcon,
                  { backgroundColor: colors.background },
                ]}
              >
                <Text style={styles.placeholderEmoji}>📹</Text>
              </View>
              <Text
                style={[styles.placeholderText, { color: colors.placeholder }]}
              >
                Initialisation de la caméra...
              </Text>
            </View>
          )}
        </View>

        {/* Bouton d'arrêt flottant */}
        <TouchableOpacity
          style={[styles.stopButton, { backgroundColor: colors.logout }]}
          onPress={handleStopLive}
          activeOpacity={0.8}
        >
          <Text style={styles.stopButtonIcon}>⏹️</Text>
          <Text style={[styles.stopButtonText, { color: colors.background }]}>
            Terminer
          </Text>
        </TouchableOpacity>
      </Animated.View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    margin: 0,
    padding: 0,
  },
  content: {
    flex: 1,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingVertical: 15,
    borderBottomWidth: 1,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
  },
  leftSection: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 15,
  },
  rightSection: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  liveIndicatorContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  liveIndicator: {
    width: 10,
    height: 10,
    borderRadius: 5,
    marginRight: 8,
  },
  liveText: {
    fontWeight: 'bold',
    fontSize: 16,
    letterSpacing: 1,
  },
  durationText: {
    fontSize: 16,
    fontWeight: '600',
    fontFamily: 'monospace',
  },
  viewersBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 20,
    gap: 6,
  },
  viewersIcon: {
    fontSize: 14,
  },
  viewersCount: {
    fontSize: 14,
    fontWeight: 'bold',
  },
  adminBadge: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 20,
  },
  adminText: {
    fontSize: 14,
    fontWeight: '600',
  },
  videoContainer: {
    flex: 1,
  },
  video: {
    flex: 1,
    width: '100%',
  },
  videoPlaceholder: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  placeholderIcon: {
    width: 120,
    height: 120,
    borderRadius: 60,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20,
    elevation: 3,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.1,
    shadowRadius: 6,
  },
  placeholderEmoji: {
    fontSize: 60,
  },
  placeholderText: {
    fontSize: 18,
    fontWeight: '500',
  },
  stopButton: {
    position: 'absolute',
    bottom: 40,
    alignSelf: 'center',
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 24,
    paddingVertical: 12,
    borderRadius: 30,
    elevation: 5,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 5 },
    shadowOpacity: 0.3,
    shadowRadius: 10,
    gap: 8,
  },
  stopButtonIcon: {
    fontSize: 18,
  },
  stopButtonText: {
    fontSize: 16,
    fontWeight: 'bold',
  },
});
