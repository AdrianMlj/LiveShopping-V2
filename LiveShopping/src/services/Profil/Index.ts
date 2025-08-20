import API from './../../config/API';
import {Asset} from 'react-native-image-picker';


interface UpdateProfileData {
  id_user: number;
  username: string;
  images: string;
  email: string;
  address?: string;
  country?: string;
  contact?: string;
}


export const uploadImage = async (image: Asset): Promise<string | null> => {
  if (!image.uri || !image.type || !image.fileName) {
    console.error("Image invalide !");
    return null;
  }

  const formData = new FormData();
  formData.append('image', {
    uri: image.uri,
    type: image.type,
    name: image.fileName,
  });

  try {
    const response = await API.post('/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    if (response.data.status === 'success') {
      return response.data.filename;
    } else {
      console.warn('Erreur serveur:', response.data.message);
      return null;
    }
  } catch (error: any) {
    console.error("Erreur lors de l'envoi de l'image :", error.response?.data || error.message);
    return null;
  }
};

export const updateProfile = async (data: UpdateProfileData) => {
  try {
    const response = await API.post('/client/profil/update', data, {
      headers: {
        'Content-Type': 'application/json',
      },
    });

    if (response.status === 200) {
      console.log('Profil mis à jour:', response.data);
      return response.data;
    } else {
      console.warn('Erreur inattendue:', response.status, response.data);
      return null;
    }
  } catch (error: any) {
    console.error('Erreur lors de la mise à jour du profil:', error.response?.data || error.message);
    throw error;
  }
};