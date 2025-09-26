import React, { useEffect, useRef } from 'react';
import { StyleSheet, Animated, TouchableOpacity, Text } from 'react-native';
import { useTranslation } from 'react-i18next';

export default function LanguageToggle() {
  const { i18n } = useTranslation();
  const isFrench = i18n.language === 'fr';

  const animation = useRef(new Animated.Value(isFrench ? 0 : 1)).current;

  useEffect(() => {
    Animated.timing(animation, {
      toValue: isFrench ? 0 : 1,
      duration: 300,
      useNativeDriver: false,
    }).start();
  }, [i18n.language, animation, isFrench]);

  const toggleLanguage = () => {
    i18n.changeLanguage(isFrench ? 'en' : 'fr');
  };

  const interpolatePosition = animation.interpolate({
    inputRange: [0, 1],
    outputRange: [2, 32], // position de glissement
  });

  const interpolateBackground = animation.interpolate({
    inputRange: [0, 1],
    outputRange: ['#DC2626', '#4968F4'],
  });

  return (
    <TouchableOpacity onPress={toggleLanguage}>
      <Animated.View
        style={[styles.toggleContainer, { backgroundColor: interpolateBackground }]}
      >
        {/* Fond avec deux drapeaux */}
        <Text style={styles.flagLeft}>🇫🇷</Text>
        <Text style={styles.flagRight}>🇬🇧</Text>

        {/* Curseur animé */}
        <Animated.View
          style={[
            styles.flagSwitcher,
            { transform: [{ translateX: interpolatePosition }] },
          ]}
        >
          <Text style={styles.flagText}>{isFrench ? '🇫🇷' : '🇬🇧'}</Text>
        </Animated.View>
      </Animated.View>
    </TouchableOpacity>
  );
}


const styles = StyleSheet.create({
  toggleContainer: {
    width: 60,
    height: 30,
    borderRadius: 15,
    padding: 2,
    flexDirection: 'row',
    alignItems: 'center',
    position: 'relative',
  },
  flagLeft: {
    position: 'absolute',
    left: 8,
    zIndex: 0,
    fontSize: 14,
  },
  flagRight: {
    position: 'absolute',
    right: 8,
    zIndex: 0,
    fontSize: 14,
  },
  flagSwitcher: {
    width: 26,
    height: 26,
    borderRadius: 13,
    backgroundColor: 'white',
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 1,
  },
  flagText: {
    fontSize: 16,
  },
});
