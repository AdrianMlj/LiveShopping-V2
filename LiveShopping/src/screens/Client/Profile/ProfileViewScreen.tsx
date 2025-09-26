import React, { useEffect } from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { useUser } from '../../../context/AuthContext';
import { useTheme } from '../../../context/ThemeContext';
import UserAvatar from '../../../components/Molecule/UserAvatar';
import { shadowBox } from '../../../utils/colors';
import { useTranslation } from 'react-i18next';

export default function ProfileViewScreen({ navigation }: any) {
  const { user } = useUser();
  const { colors } = useTheme();
    const { t } = useTranslation();

  useEffect(() => {
    if (!user) {
      navigation.navigate('signin');
    }
  }, [user, navigation]);

  if (!user) return null;

  return (
    <View style={[styles.container, { backgroundColor: colors.background }]}>
      <View style={styles.header}>
        <TouchableOpacity
          onPress={() => navigation.goBack()}
          style={styles.backButton}
        >
          <Text style={[styles.backArrow, {color: colors.text}]}>←</Text>
        </TouchableOpacity>
      </View>
      <View style={[styles.avatarCard, shadowBox, { backgroundColor: colors.surface }]}>
        <View style={styles.avatarWrapper}>
          <UserAvatar
            size={100}
            username={user.username}
            image={user.images}
          />
          <TouchableOpacity
            style={[styles.editIcon, { backgroundColor: colors.surface }]}
            onPress={() => navigation.navigate('profileEdit')}
          >
            <Text style={{ fontSize: 20, color: colors.placeholder }}>✏️</Text>
          </TouchableOpacity>
        </View>
      </View>

      <View style={[styles.infoCard, shadowBox, { backgroundColor: colors.surface }]}>
        <ProfileField label={t("Username")} value={user.username} />
        <ProfileField label={t("Email")} value={user.email} />
        <ProfileField label={t("Contact")} value={user.contact} />
        <ProfileField label={t("address")} value={user.address} />
        <ProfileField label={t("Country")} value={user.country} />
      </View>
    </View>
  );
}

const ProfileField = ({ label, value }: { label: string; value?: string }) => {
  const { colors } = useTheme();

  return (
    <View style={{ marginBottom: 15 }}>
      <Text style={{ fontWeight: 'bold', marginBottom: 2, color: colors.text }}>
        {label}
      </Text>
      <Text style={{ color: colors.text }}>
        {value || 'Non renseigné'}
      </Text>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    paddingTop: 50,
    alignItems: 'center',
    paddingHorizontal: 20,
  },
  avatarCard: {
    width: '100%',
    backgroundColor: '#fff',
    borderRadius: 20,
    paddingVertical: 30,
    alignItems: 'center',
    marginBottom: 30,
  },
  avatarWrapper: {
    position: 'relative',
    justifyContent: 'center',
    alignItems: 'center',
  },
  editIcon: {
    position: 'absolute',
    bottom: 0,
    right: -5,
    borderRadius: 20,
    padding: 6,
  },
  infoCard: {
    width: '100%',
    backgroundColor: '#fff',
    borderRadius: 20,
    padding: 25,
    marginBottom: 30,
  },
  logoutBtn: {
    width: '100%',
    padding: 15,
    borderRadius: 15,
    alignItems: 'center',
    backgroundColor: '#fff',
  },
  header: {
    width: '100%',
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 20,
  },
  backButton: {
    paddingHorizontal: 10,
  },
  backArrow: {
    fontSize: 30,
    fontWeight: 'bold',
  },
});
