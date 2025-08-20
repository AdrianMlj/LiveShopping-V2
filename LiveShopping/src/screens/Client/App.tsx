import React, { useEffect } from 'react';
import {
  NavigationIndependentTree,
} from '@react-navigation/native';
import NavClient from './../../navigation/NavClient';
import { StyleSheet, View } from 'react-native';
import { useUser } from '../../context/AuthContext';

const AppClient = ({ navigation }: any) => {
  const { user } = useUser();

  useEffect(() => {
    if (!user) {
      navigation.navigate('signin');
    }
  }, [user, navigation]);
  return (
    <NavigationIndependentTree>
        <View style={styles.container}>
          <NavClient />
        </View>
    </NavigationIndependentTree>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
});

export default AppClient;
