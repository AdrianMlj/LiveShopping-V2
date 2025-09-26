import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import LiveStream from './LiveStream';
import { startLive, stopLive } from '../../../services/Live/LiveService';
import { useUser } from '../../../context/AuthContext';

export default function LiveScreen() {
  const { user } = useUser();
  const [id_live, setIdLive] = useState<number>(0);
  const [start, setStart] = useState(false);

  const starterLive = async () => {
  try {
    console.log("Démarrage du live pour user:", user?.id_user);
    const live = await startLive(user?.id_user);
    console.log("Réponse startLive:", live);

    if (!live || !live.live_id) {
      console.warn("⚠ Aucun live_id reçu, impossible de démarrer");
      return;
    }

    setIdLive(live.live_id);
    setStart(true);
  } catch (error) {
    console.error("Erreur lors du démarrage du live:", error);
  }
};

  const stoperLive = async () => {
    await stopLive(user?.id_user, id_live);
    setStart(false);
  };

  return (
    <View style={styles.container}>
      {!start ? (
        <TouchableOpacity onPress={starterLive}>
          <Text style={styles.title}>🚀 Démarrer le Live</Text>
        </TouchableOpacity>
      ) : (
        <LiveStream adminId={String(user?.id_user)} onStopLive={stoperLive}/>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 0},
  title: { fontSize: 24, fontWeight: 'bold' },
});
