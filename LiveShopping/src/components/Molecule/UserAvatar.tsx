import React from 'react';
import { View, Text, Image, StyleSheet } from 'react-native';

type Props = {
  username: string;
  image?:      | null | undefined;
  size?: number;
};

const UserAvatar = ({ username, image, size = 60 }: Props) => {
  const initials = username?.charAt(0)?.toUpperCase() || '?';

  return (
    <View style={[styles.avatarContainer, { width: size, height: size, borderRadius: size / 2 }]}>
      {image ? (
        <Image
          source={{ uri: `http://192.168.88.15:8000/uploads/${image}` }}
          style={[styles.avatarImage, { width: size, height: size, borderRadius: size / 2 }]}
        />
      ) : (
        <View style={[styles.initialsCircle, { width: size, height: size, borderRadius: size / 2 }]}>
          <Text style={styles.initialsText}>{initials}</Text>
        </View>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  avatarContainer: {
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#ccc',
    overflow: 'hidden',
  },
  avatarImage: {
    resizeMode: 'cover',
  },
  initialsCircle: {
    backgroundColor: '#4968F4',
    justifyContent: 'center',
    alignItems: 'center',
  },
  initialsText: {
    color: '#fff',
    fontSize: 24,
    fontWeight: 'bold',
  },
});

export default UserAvatar;
