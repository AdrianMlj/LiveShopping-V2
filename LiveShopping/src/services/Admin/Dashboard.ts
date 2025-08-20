import API from './../../config/API';

export const getDashboardData = async (id_user: number | undefined) => {
  try {
    const response = await API.post('/dashboard', { id_user: id_user }, {
      headers: {
        'Content-Type': 'application/json',
      },
    });

    if (response.status === 200) {
      console.log('Données dashboard:', response.data);
      return response.data;
    } else {
      console.warn('Erreur inattendue:', response.status, response.data);
      return null;
    }
  } catch (error: any) {
    console.error('Erreur lors de la récupération du dashboard:', error.response?.data || error.message);
    throw error;
  }
};
