import React, {  useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Alert,
  TouchableOpacity,
} from 'react-native';
import InputField from '../../components/InputField';
import PrimaryButton from '../../components/PrimaryButton';
import { useTheme } from '../../context/ThemeContext';
import { login } from '../../services/Auth/authService';
import { useUser } from '../../context/AuthContext';

export default function SignInScreen({ navigation }: any) {
  const { colors } = useTheme();
  const {  setUser } = useUser();

  const [form, setForm] = useState({
    username: '',
    password: '',
  });

  const handleChange = (key: string, value: string) => {
    setForm(prev => ({ ...prev, [key]: value }));
  };

  const handleSubmit = async () => {
    if (!form.username || !form.password) {
      Alert.alert('Erreur', 'Veuillez remplir les champs obligatoires');
      return;
    }

    try {
      const data = await login(form);
      console.log('Login successful:', data);
      setUser(data.user);
      if (data.user.is_seller === true) {
        navigation.navigate('seller');
      }
      else {
        navigation.navigate('client'); 
      }
    } catch (error: any) {

      if (error.response) {
        console.log('Erreur réponse:', error.response.data);
      } else if (error.request) {
        console.log('Erreur requête sans réponse:', error.request);
      } else {
        console.log('Erreur autre:', error.message);
      }
      Alert.alert(
        'Erreur',
        error.response?.data?.message || "Erreur lors de lA connection",
      );
    }
  };

  return (
    <View style={[styles.container, { backgroundColor: colors.primary }]}>
      <View style={[styles.formContainer, { backgroundColor: colors.surface }]}>
        <Text style={[styles.title, { color: colors.text }]}>
          Sign <Text style={{ color: colors.primary }}>In</Text>
        </Text>
        <Text style={[styles.subtitle, { color: colors.text }]}>
          Login to your Account
        </Text>
        <InputField
          placeholder="username"
          placeholderTextColor={colors.placeholder}
          onChangeText={text => handleChange('username', text)}
        />
        <InputField
          placeholder="Password"
          secureTextEntry
          placeholderTextColor={colors.placeholder}
          onChangeText={text => handleChange('password', text)}
        />
        <PrimaryButton title="Sign in" onPress={handleSubmit} />
        <Text style={[styles.linkText, { color: colors.placeholder }]}>
          Need an account?{' '}
          <Text
            style={[styles.linkAccent, { color: colors.textLink }]}
            onPress={() => navigation.navigate('signup')}
          >
            Sign up here
          </Text>
        </Text>
        <TouchableOpacity onPress={() => navigation.navigate('signup')}>
          <Text style={[styles.forgot, { color: colors.textLink }]}>
            Forgot your password ?
          </Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flexGrow: 1,
    paddingBottom: 0,
  },
  formContainer: {
    marginTop: '45%',
    padding: 24,
    width: '100%',
    height: 665,
    borderTopLeftRadius: 50,
    borderTopRightRadius: 50,

    shadowColor: 'rgba(0, 0, 0, 0.25)',
    shadowOffset: {
      width: 0,
      height: -5,
    },
    shadowOpacity: 0.25,
    shadowRadius: 10,
    elevation: 10,
  },
  title: {
    marginTop: 50,
    marginBottom: 50,
    fontSize: 40,
    fontWeight: 'bold',
    textAlign: 'center',
  },
  subtitle: {
    marginTop: 8,
    fontSize: 14,
  },
  linkText: {
    marginTop: 60,
    textAlign: 'center',
    fontSize: 16,
    marginBottom: 10,
    color: '#555',
  },
  linkAccent: {
    color: '#4968f4',
    fontWeight: 'bold',
  },
  forgot: {
    marginTop: 8,
    textAlign: 'center',
    fontSize: 15,
    color: '#000',
    fontWeight: '600',
  },
});
