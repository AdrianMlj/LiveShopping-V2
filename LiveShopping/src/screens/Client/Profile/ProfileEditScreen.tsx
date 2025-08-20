import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TextInput,
  TouchableOpacity,
  ScrollView,
  Alert,
} from 'react-native';
import { useUser } from '../../../context/AuthContext';
import { useTheme } from '../../../context/ThemeContext';
import UserAvatar from '../../../components/Molecule/UserAvatar';
import { shadowBox } from '../../../utils/colors';
import { launchImageLibrary } from 'react-native-image-picker';
import { uploadImage, updateProfile } from '../../../services/Profil/Index';
import { useTranslation } from 'react-i18next';

export default function ProfileEditInline({ navigation }: any) {
  const { user, setUser } = useUser();
  const { colors } = useTheme();
    const { t } = useTranslation();

  const [fields, setFields] = useState({
    id_user: 0,
    username: '',
    images: '',
    email: '',
    contact: '',
    address: '',
    country: '',
  });

  useEffect(() => {
    if (user) {
      setFields({
        id_user: user.id_user || 0,
        username: user.username || '',
        images: user.images || '',
        email: user.email || '',
        contact: user.contact || '',
        address: user.address || '',
        country: user.country || '',
      });
    }
  }, [user]);

  const [editableFields, setEditableFields] = useState({
    username: false,
    email: false,
    contact: false,
    address: false,
    country: false,
  });

  if (!user) return null;

  const handleEdit = (key: keyof typeof editableFields) => {
    setEditableFields({ ...editableFields, [key]: true });
  };

  const handleChange = (key: keyof typeof fields, value: string) => {
    setFields({ ...fields, [key]: value });
  };

  const handleSelectImage = () => {
    launchImageLibrary(
      {
        mediaType: 'photo',
        quality: 0.7,
      },
      async response => {
        if (response.didCancel) return;

        if (response.errorCode) {
          Alert.alert('Erreur', response.errorMessage || 'Erreur inconnue');
          return;
        }

        const asset = response.assets?.[0];
        if (asset?.uri) {
          console.log('Image sélectionnée:', asset.uri);
          user.images = asset.uri;

          const uploadedUrl = await uploadImage(asset);

          if (uploadedUrl) {
            user.images = uploadedUrl;
            fields.images = uploadedUrl;
            console.log('image :', user.images);
            
            setUser(user);
            Alert.alert('Succès', 'Image envoyée avec succès !');
          } else {
            Alert.alert('Échec', "Échec de l'envoi de l'image.");
          }
        }
      },
    );
  };

  const saveChanges = async () => {
    try {
      const dataToUpdate = {
        ...fields,
      };
      console.log(dataToUpdate);

      const updated = await updateProfile(dataToUpdate);
      setUser(updated.user);
      Alert.alert('Succès', 'Profil mis à jour !');
      setEditableFields({
        username: false,
        email: false,
        contact: false,
        address: false,
        country: false,
      });
    } catch (error) {
      console.error('Erreur mise à jour :', error);
      Alert.alert('Erreur', 'Impossible de mettre à jour le profil');
    }
  };

  return (
    <ScrollView
      contentContainerStyle={[
        styles.container,
        { backgroundColor: colors.background },
      ]}
    >
      <View style={styles.header}>
        <TouchableOpacity
          onPress={() => navigation.goBack()}
          style={styles.backButton}
        >
          <Text style={[styles.backArrow, {color: colors.text}]}>←</Text>
        </TouchableOpacity>
      </View>
      <TouchableOpacity
        onPress={handleSelectImage}
        style={[styles.card, { backgroundColor: colors.surface, ...shadowBox }]}
      >
        <UserAvatar username={user.username} image={user.images} size={100} />
      </TouchableOpacity>

      <View
        style={[
          styles.infoCard,
          { backgroundColor: colors.surface, ...shadowBox },
        ]}
      >
        {Object.keys(fields)
          .filter(key => key !== 'id_user' && key !== 'images')
          .map(key => (
            <View key={key} style={styles.fieldRow}>
              <View style={{ flex: 1 }}>
                <Text style={[styles.label, { color: colors.text }]}>
                  {t(key.charAt(0).toUpperCase() + key.slice(1))}
                </Text>
                <TextInput
                  style={[styles.input, { color: colors.text }]}
                  value={fields[key as keyof typeof fields]}
                  editable={editableFields[key as keyof typeof editableFields]}
                  onChangeText={text =>
                    handleChange(key as keyof typeof fields, text)
                  }
                />
              </View>
              {!editableFields[key as keyof typeof editableFields] && (
                <TouchableOpacity
                  onPress={() => handleEdit(key as keyof typeof editableFields)}
                >
                  <Text style={styles.editIcon}>✏️</Text>
                </TouchableOpacity>
              )}
            </View>
          ))}

        <TouchableOpacity style={styles.saveButton} onPress={saveChanges}>
          <Text style={styles.saveButtonText}>Enregistrer</Text>
        </TouchableOpacity>
      </View>
    </ScrollView>
  );
}

// export default ProfileEditInline;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    paddingTop: 50,
    alignItems: 'center',
    paddingHorizontal: 20,
  },
  card: {
    padding: 20,
    borderRadius: 20,
    marginBottom: 20,
  },
  infoCard: {
    width: '90%',
    borderRadius: 20,
    padding: 20,
  },
  fieldRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 20,
  },
  label: {
    fontWeight: 'bold',
    fontSize: 14,
    marginBottom: 5,
  },
  input: {
    fontSize: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#ccc',
    paddingVertical: 4,
  },
  editIcon: {
    fontSize: 18,
    marginLeft: 10,
  },
  saveButton: {
    marginTop: 20,
    padding: 15,
    backgroundColor: '#4968F4',
    borderRadius: 10,
    alignItems: 'center',
  },
  saveButtonText: {
    color: 'white',
    fontWeight: 'bold',
    fontSize: 16,
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
