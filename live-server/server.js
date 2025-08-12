const WebSocket = require('ws');

const wss = new WebSocket.Server({ port: 8080 });

let streamer = null; // stocke la connexion du streamer
const viewers = new Set(); // stocke les connexions des viewers

wss.on('connection', function connection(ws, req) {
  ws.on('message', function message(data) {
    const msg = JSON.parse(data);

    if (msg.type === 'streamer') {
      streamer = ws;
      console.log('Streamer connecté');
    } else if (msg.type === 'viewer') {
      viewers.add(ws);
      console.log('Viewer connecté');
      // préviens le streamer qu’il y a un viewer
      if (streamer) streamer.send(JSON.stringify({ type: 'newViewer' }));
    } else if (msg.type === 'offer') {
      // streamer envoie une offre pour un viewer, on la route au viewer
      for (let viewer of viewers) {
        if (viewer === ws) continue;
        if (viewer.readyState === WebSocket.OPEN) {
          viewer.send(JSON.stringify({ type: 'offer', offer: msg.offer }));
        }
      }
    } else if (msg.type === 'answer') {
      // viewer répond au streamer
      if (streamer && streamer.readyState === WebSocket.OPEN) {
        streamer.send(JSON.stringify({ type: 'answer', answer: msg.answer }));
      }
    } else if (msg.type === 'candidate') {
      // relay ICE candidates entre streamer et viewers
      // à adapter selon qui envoie
      if (msg.target === 'streamer' && streamer) {
        streamer.send(JSON.stringify({ type: 'candidate', candidate: msg.candidate }));
      } else {
        for (let viewer of viewers) {
          if (viewer.readyState === WebSocket.OPEN) {
            viewer.send(JSON.stringify({ type: 'candidate', candidate: msg.candidate }));
          }
        }
      }
    }
  });

  ws.on('close', () => {
    if (ws === streamer) {
      streamer = null;
      console.log('Streamer déconnecté');
    } else {
      viewers.delete(ws);
      console.log('Viewer déconnecté');
    }
  });
});

console.log('Serveur WebSocket démarré sur ws://localhost:8080');
