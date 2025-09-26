import { View, Text, StyleSheet, ActivityIndicator, Image } from 'react-native';
import React, { useEffect } from 'react';

export default function SplashScreen({navigation}: any) {
  useEffect(() => {
    const timer = setTimeout(() => {
      navigation.navigate('signin');
    }, 3000);

    return () => clearTimeout(timer); // Nettoyage si le composant est démonté avant 3s
  }, [navigation]);
  return (
    <View style={styles.container}>
      <Image
        source={require('../assets/logo.png')} // Remplacez par le chemin de votre logo
        style={styles.logo}
        resizeMode="contain"
      />
      <Text style={styles.title}>
        Live
        <Text style={styles.titlenext}>Shopping</Text>
      </Text>
      <ActivityIndicator size="large" color="#007AFF" style={styles.loader} />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff',
    alignItems: 'center',
    justifyContent: 'center',
  },
  logo: {
    width: 120,
    height: 120,
    marginBottom: 30,
  },
  title: {
    fontSize: 32,
    fontWeight: 'bold',
    color: '#007AFF',
    marginBottom: 20,
  },
  loader: {
    marginTop: 20,
  },
  titlenext: {
    color: '#000000ff',
  },
});