import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Image } from 'react-native';
import { useTheme } from '../../../context/ThemeContext';
import DashboardCharts from './DashboardCharts';

export default function ClientHomeScreen({ navigation }: any) {
  const { colors } = useTheme();
  return (
    <View style={[styles.container, { backgroundColor: colors.background }]}>
      <View style={styles.head}>
        <Text style={[styles.title, { color: colors.text }]}>
          Sign <Text style={{ color: colors.primary }}>Up</Text>
        </Text>
        <View style={styles.header_icon}>
          <TouchableOpacity
            onPress={() => navigation.navigate('profileView')}
            style={styles.header_card}
          >
            <Image
              source={require('../../../assets/icons/notif.png')}
              resizeMode="contain"
              style={[{width: 25, height: 25,}]}
            />
          </TouchableOpacity>
          <TouchableOpacity
            onPress={() => navigation.navigate('profileView')}
            style={styles.header_card}
          >
            <Image
              source={require('../../../assets/icons/chat.png')}
              resizeMode="contain"
              style={[{width: 25, height: 25,}]}
            />
          </TouchableOpacity>
        </View>
      </View>
        <DashboardCharts/>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    paddingTop: '10%',
    paddingHorizontal: '10%',
  },
  title: { fontSize: 24, fontWeight: 'bold' },
  head: {
    display: 'flex',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginRight: 20
  },
  header_card: {
    width: 40
  },
  header_icon: {
    width: 30,
    display: 'flex',
    flexDirection: 'row',
  }
});
