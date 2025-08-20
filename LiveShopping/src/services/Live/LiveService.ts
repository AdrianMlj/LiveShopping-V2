import API from './../../config/API';

export const startLive = async (userId: number | undefined) => {
  try {
    console.log("📡 Requête startLive avec:", { user_id: userId });
    const response = await API.post('/live/start', { user_id: userId });
    console.log("✅ Réponse startLive:", response.data);
    return response.data;
  } catch (error: any) {
    if (error.response) {
      console.error("❌ Erreur backend:", {
        status: error.response.status,
        data: error.response.data
      });
    } else if (error.request) {
      console.error("⚠ Pas de réponse serveur:", error.request);
    } else {
      console.error("💥 Erreur Axios:", error.message);
    }
    throw new Error(error || 'Erreur lors du démarrage du live');
  }
};


export const stopLive = async (userId: number | undefined, liveId: number) => {
  try {
    const response = await API.post('/live/stop', { user_id: userId, live_id: liveId });
    return response.data;
  } catch (error: any) {
    throw new Error(error.response?.data?.error || 'Erreur lors de l\'arrêt du live');
  }
};


export const getLiveStream = async (liveId: number) => {
  try {
    const response = await API.get(`/live/stream/${liveId}`);
    return response.data;
  } catch (error: any) {
    throw new Error(error.response?.data?.error || 'Erreur lors de la récupération du flux');
  }
}

export const getLiveList = async () => {
  try {
    const response = await API.get('/live/active');
    return response.data;
  } catch (error: any) {
    throw new Error(error.response?.data?.error || 'Erreur lors de la récupération de la liste des lives');
  }
}