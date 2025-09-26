import API from './../../config/API';

interface SignUpData {
  username: string;
  email: string;
  password: string;
  contact?: string;
  address?: string;
  country?: string;
}

interface SignInData {
  username: string;
  password: string;
}

export const login = async (data: SignInData) => {
  try {
    const response = await API.post('/login', data);
    return response.data;
  } catch (error: any) {
    throw new Error(
      error.response?.data?.message || 'Erreur de connexion'
    );
  }
};

export const signUp = async (data: SignUpData) => {
  try {
    const response = await API.post('/inscription', data);
    return response.data;
  } catch (error) {
    throw error;
  }
};