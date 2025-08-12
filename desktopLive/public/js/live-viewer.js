const signalingServer = new WebSocket('ws://localhost:8080');
let peerConnection;

signalingServer.onopen = () => {
  signalingServer.send(JSON.stringify({ type: 'viewer' }));
};

signalingServer.onmessage = async (event) => {
  const data = JSON.parse(event.data);

  if (data.type === 'offer') {
    peerConnection = new RTCPeerConnection();

    peerConnection.ontrack = event => {
      document.getElementById('viewerVideo').srcObject = event.streams[0];
    };

    peerConnection.onicecandidate = e => {
      if (e.candidate) {
        signalingServer.send(JSON.stringify({
          type: 'candidate',
          candidate: e.candidate,
          target: 'streamer'
        }));
      }
    };

    await peerConnection.setRemoteDescription(new RTCSessionDescription(data.offer));
    const answer = await peerConnection.createAnswer();
    await peerConnection.setLocalDescription(answer);

    signalingServer.send(JSON.stringify({
      type: 'answer',
      answer: answer
    }));
  } else if (data.type === 'candidate') {
    if (peerConnection) await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
  }
};
