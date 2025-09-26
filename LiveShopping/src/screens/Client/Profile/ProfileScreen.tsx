import React, { useEffect } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Image } from 'react-native';
import { useTheme } from '../../../context/ThemeContext';
import ThemeToggle from '../../../components/Atom/inputTheme';
import LanguageToggle from '../../../components/Atom/LanguageToggle';
import DropdownParameters from '../../../components/Molecule/DropdownParameters';
import { useTranslation } from 'react-i18next';
import { shadowBox } from './../../../utils/colors';
import { useUser } from '../../../context/AuthContext';
import UserAvatar from '../../../components/Molecule/UserAvatar';

export default function ProfileScreen({ navigation }: any) {
  const { user, logout } = useUser();
  const { colors } = useTheme();
  const { t } = useTranslation();

  const handleChange = () => {
    navigation.navigate('profileView');
  }

  useEffect(() => {
      console.log('User updated:', user);
    if (!user) {
      navigation.navigate('signin');  
    }
  }, [user, navigation]);
  
  if (!user) {
    return null;
  }
  return (
    <View style={[styles.container, { backgroundColor: colors.background }]}>
      <TouchableOpacity
        onPress={handleChange}
        style={[styles.card, { backgroundColor: colors.surface, ...shadowBox }]}
      >
        <UserAvatar username={user.username} image={user.images} size={40} />
        <Text style={[styles.username, { color: colors.text }]}>
          {user.username}
        </Text>
      </TouchableOpacity>
      <DropdownParameters title={t('parameters')}>
        <View style={[styles.Parameters, { backgroundColor: colors.surface }]}>
          <Text style={[styles.title, { color: colors.text }]}>
            {t('themeChange')}
          </Text>
          <ThemeToggle />
        </View>
        <View style={[styles.Parameters, { backgroundColor: colors.surface }]}>
          <Text style={[styles.title, { color: colors.text }]}>
            {t('languageChange')}
          </Text>
          <LanguageToggle />
        </View>
      </DropdownParameters>
      <TouchableOpacity
        onPress={logout}
        style={[styles.card, { backgroundColor: colors.surface }, {justifyContent: 'space-between'}]}
      >
        <Text style={[styles.title, { color: colors.logout }]}>
          {t('logout')}
        </Text>
        <Image
          source={require('../../../assets/icons/logout.png')}
          style={{ width: 20, height: 20 }}
        />
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    paddingTop: 50,
  },
  card: {
    padding: 20,
    borderColor: 'rgba(0, 0, 0, 0.5)',
    marginTop: 20,
    width: '95%',
    display: 'flex',
    borderRadius: 15,
    marginHorizontal: '2.5%',
    flexDirection: 'row',
    alignItems: 'center',
  },
  Parameters: {
    padding: 20,
    marginTop: 20,
    width: '95%',
    display: 'flex',
    borderRadius: 15,
    marginHorizontal: '2.5%',
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  username: {
    marginLeft: 15,
    fontSize: 20,
    fontWeight: 'bold'
  },
  title: {
    fontSize: 20,
    fontWeight: 'bold'
  },
});
