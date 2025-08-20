import React, { useState } from 'react';
import { Text, StyleSheet, View, Alert } from 'react-native';
import InputField from '../../components/InputField';
import InputAutocomplete from '../../components/InputSelect';
import PrimaryButton from '../../components/PrimaryButton';
import { useTheme } from '../../context/ThemeContext';
import { signUp } from '../../services/Auth/authService'; // Assure-toi que ce chemin est bon

export default function SignUpScreen({ navigation }: any) {
  const { colors } = useTheme();

  const [form, setForm] = useState({
    username: '',
    email: '',
    password: '',
    contact: '',
    address: '',
    country: '',
  });

  const handleChange = (key: string, value: string) => {
    setForm((prev) => ({ ...prev, [key]: value }));
  };

  const handleSubmit = async () => {
    if (!form.username || !form.email || !form.password) {
      Alert.alert('Erreur', 'Veuillez remplir les champs obligatoires');
      return;
    }

    try {
      console.log('Données du formulaire avant l\'envoi:', form); // 👈 ajoute ça
      const data = await signUp(form);
      Alert.alert('Succès', 'Inscription réussie');
      console.log('Inscription réussie:', data);
      // navigation.navigate('signin');
    } catch (error: any) {
      console.log('Erreur Axios complète:', error); // 👈 ajoute ça

      if (error.response) {
        console.log('Erreur réponse:', error.response.data); // 👈 ici aussi
      } else if (error.request) {
        console.log('Erreur requête sans réponse:', error.request);
      } else {
        console.log('Erreur autre:', error.message);
      }
      Alert.alert(
        'Erreur',
        error.response?.data?.message || "Erreur lors de l'inscription"
      );
    }
  };

  return (
    <View style={[styles.container, { backgroundColor: colors.surface }]}>
      <Text style={[styles.title, { color: colors.text }]}>
        Sign <Text style={{ color: colors.primary }}>Up</Text>
      </Text>
      <Text style={[styles.subtitle, { color: colors.text }]}>
        Create your Account
      </Text>

      <InputField
        placeholder="Username"
        placeholderTextColor={colors.placeholder}
        value={form.username}
        onChangeText={(text) => handleChange('username', text)}
      />
      <InputField
        placeholder="Email"
        placeholderTextColor={colors.placeholder}
        keyboardType="email-address"
        value={form.email}
        onChangeText={(text) => handleChange('email', text)}
      />
      <InputField
        placeholder="Contact"
        placeholderTextColor={colors.placeholder}
        value={form.contact}
        onChangeText={(text) => handleChange('contact', text)}
      />
      <InputField
        placeholder="Address"
        placeholderTextColor={colors.placeholder}
        value={form.address}
        onChangeText={(text) => handleChange('address', text)}
      />

      <InputAutocomplete
        options={[
          { label: 'Madagascar', value: 'mg' },
          { label: 'France', value: 'fr' },
          { label: 'Germany', value: 'de' },
        ]}
        initialValue={
          ['mg', 'fr', 'de'].includes(form.country)
            ? { mg: 'Madagascar', fr: 'France', de: 'Germany' }[form.country]
            : ''
        }
        onSelect={(value) => handleChange('country', value)}
      />

      <InputField
        placeholder="Password"
        secureTextEntry
        placeholderTextColor={colors.placeholder}
        value={form.password}
        onChangeText={(text) => handleChange('password', text)}
      />

      <PrimaryButton title="Sign Up" onPress={handleSubmit} />

      <Text style={[styles.linkText, { color: colors.text }]}>
        Already have an account?{' '}
        <Text
          style={[styles.linkAccent, { color: colors.primary }]}
          onPress={() => navigation.navigate('signin')}
        >
          Sign in here
        </Text>
      </Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    paddingTop: 0,
    padding: 24,
  },
  title: {
    marginTop: 50,
    marginBottom: 40,
    fontSize: 40,
    fontWeight: 'bold',
    textAlign: 'center',
  },
  titleAccent: {
    color: '#4968f4',
  },
  subtitle: {
    marginTop: 0,
    fontSize: 14,
    color: '#333',
  },
  linkText: {
    marginTop: 40,
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
    fontSize: 16,
    color: '#000',
    fontWeight: '600',
  },
});
