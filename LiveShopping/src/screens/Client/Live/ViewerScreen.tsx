import React, { useEffect, useRef, useState } from "react";
import { RTCPeerConnection } from "react-native-webrtc";

type LiveProps = {
  seller?: { id: number; username: string; images?: string };
  nbrLike?: number;
  endLive?: boolean;
};

const LivePage: React.FC<LiveProps> = ({ seller, nbrLike = 0, endLive }) => {
  const videoRef = useRef<HTMLVideoElement>(null);
  const [likeCount, setLikeCount] = useState(nbrLike);

  const likeLive = () => setLikeCount(likeCount + 1);

  useEffect(() => {
    const videoEl = videoRef.current;
    if (!videoEl) return;

    let pc: RTCPeerConnection | null = null;
    let signaling: WebSocket | null = null;

    const params = new URLSearchParams(window.location.search);
    const forcedSignalHost = params.get("signal");
    const proto = window.location.protocol === "https:" ? "wss" : "ws";
    const altProto = proto === "wss" ? "ws" : "wss";
    const host = window.location.hostname || "localhost";
    const port = 9090;
    const baseHosts = [host, "localhost", "127.0.0.1", "192.168.88.28"];
    const hosts = Array.from(
      new Set([...(forcedSignalHost ? [forcedSignalHost] : []), ...baseHosts])
    );
    const urls: string[] = [];
    hosts.forEach((h) => {
      urls.push(`${proto}://${h}:${port}`);
      urls.push(`${altProto}://${h}:${port}`);
    });

    function connectWS(url: string, onFail: () => void) {
      try {
        const ws = new WebSocket(url);
        ws.onerror = () => onFail && onFail();
        return ws;
      } catch {
        onFail && onFail();
        return null;
      }
    }

    let idx = 0;
    function tryNext() {
      if (idx >= urls.length) {
        console.error("[CLIENT-LIVE] Toutes les tentatives ont échoué");
        return;
      }
      const url = urls[idx++];
      console.log("[CLIENT-LIVE] WS connecting", url);
      signaling = connectWS(url, tryNext);
    }
    tryNext();
    if (!signaling) return;

    function createPeerConnection() {
      pc = new RTCPeerConnection({
        iceServers: [{ urls: "stun:stun.l.google.com:19302" }],
      });
      pc.ontrack = (event) => {
        if (event.streams && event.streams[0]) {
          videoEl.srcObject = event.streams[0];
        }
      };
      pc.onicecandidate = (e) => {
        if (e.candidate && signaling) {
          signaling.send(
            JSON.stringify({
              type: "candidate",
              candidate: e.candidate,
              target: "streamer",
              viewerId: (signaling as any).viewerId,
            })
          );
        }
      };
    }

    signaling.onopen = () => {
      const viewerId = Math.random().toString(36).slice(2);
      (signaling as any).viewerId = viewerId;
      const adminId = seller?.id ?? 0;
      signaling?.send(
        JSON.stringify({ type: "viewer", viewerId, adminId })
      );
    };

    signaling.onmessage = async (event) => {
      const data = JSON.parse(event.data);
      try {
        if (data.type === "offer") {
          if (!pc) createPeerConnection();
          await pc!.setRemoteDescription(new RTCSessionDescription(data.offer));
          const answer = await pc!.createAnswer();
          await pc!.setLocalDescription(answer);
          signaling?.send(
            JSON.stringify({
              type: "answer",
              answer,
              viewerId: (signaling as any).viewerId,
            })
          );
        } else if (data.type === "candidate" && pc) {
          await pc.addIceCandidate(new RTCIceCandidate(data.candidate));
        } else if (data.type === "end") {
          videoEl.pause();
          if (videoEl.srcObject) {
            (videoEl.srcObject as MediaStream)
              .getTracks()
              .forEach((t) => t.stop());
            videoEl.srcObject = null;
          }
        }
      } catch (err) {
        console.error("[CLIENT-LIVE] Erreur WebRTC:", err);
      }
    };

    return () => {
      try {
        pc?.close();
      } catch {}
      try {
        signaling?.close();
      } catch {}
    };
  }, [seller]);

  return (
    <div className="page">
      <div>
        <div className="player-card">
          <div className="player-thumb">
            <video
              ref={videoRef}
              autoPlay
              muted
              playsInline
              controls
              poster={
                seller?.images
                  ? `/uploads/${seller.images}`
                  : "/uploads/6891e6164b5d5.jpg"
              }
            ></video>
            {!endLive && <div className="live-badge">Live</div>}
            <div className="viewer-group">
              <div className="viewer-icons">
                {/* SVG icons */}
              </div>
              <div className="viewer-pill">{likeCount}</div>
            </div>
          </div>
          <div className="below-bar">
            <div>
              <div className="meta">
                <div className="avatar">
                  <img
                    src={
                      seller?.images
                        ? `/uploads/${seller.images}`
                        : "/uploads/6891f6e39d5a3.jpg"
                    }
                    alt="avatar"
                  />
                </div>
                <div className="username-chip">
                  {seller?.username || "Username"}
                </div>
              </div>
              <div className="follow-wrap">
                <button className="btn btn-follow">Follow</button>
              </div>
            </div>
            <div className="right-actions">
              <button className="btn btn-like" onClick={likeLive}>
                Like <span>{likeCount}</span>
              </button>
              <button className="btn btn-cart">Cart</button>
            </div>
          </div>
        </div>
        <div className="description">
          <div className="desc-title">Description</div>
          <p>Live de {seller?.username}</p>
        </div>
      </div>
      <aside>
        <div className="products">{/* produits liés au live */}</div>
      </aside>
    </div>
  );
};

export default LivePage;
