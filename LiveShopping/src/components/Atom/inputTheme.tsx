import React, { useEffect, useRef } from 'react';
import { StyleSheet, Animated, TouchableOpacity } from 'react-native';
import { useTheme } from '../../context/ThemeContext';

export default function ThemeToggle() {
  const { isDark, toggleTheme } = useTheme();
  const animation = useRef(new Animated.Value(isDark ? 1 : 0)).current;

  useEffect(() => {
    Animated.timing(animation, {
      toValue: isDark ? 1 : 0,
      duration: 300,
      useNativeDriver: false,
    }).start();
  }, [isDark, animation]);

  const interpolateTogglePosition = animation.interpolate({
    inputRange: [0, 1],
    outputRange: [2, 32],
  });

  const interpolateBackground = animation.interpolate({
    inputRange: [0, 1],
    outputRange: ['#DC2626', '#4968F4'],
  });

  return (
    <TouchableOpacity onPress={toggleTheme}>
      <Animated.View
        style={[
          styles.toggleContainer,
          { backgroundColor: interpolateBackground },
        ]}
      >
        <Animated.View
          style={[
            styles.circle,
            { transform: [{ translateX: interpolateTogglePosition }] },
          ]}
        />
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
    justifyContent: 'center',
  },
  circle: {
    width: 26,
    height: 26,
    borderRadius: 13,
    backgroundColor: 'white',
  },
});
