import React, { createContext, useContext, ReactNode, useState, useCallback, useEffect } from 'react';
import { Colors, ThemeColors } from '../utils/colors';
import {
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';

interface ThemeContextProps {
  colors: ThemeColors;
  isDark: boolean;
  toggleTheme?: () => void;
}

const ThemeContext = createContext<ThemeContextProps>({
  colors: Colors.light,
  isDark: false,
});

interface ThemeProviderProps {
  children: ReactNode;
}

export const ThemeProvider = ({ children }: ThemeProviderProps) => {
  const [isDark, setIsDark] = useState(false);
  const colors = isDark ? Colors.dark : Colors.light;

  const changeTheme = useCallback(async (theme: string) => {
    const dark = theme === 'dark';
    setIsDark(dark);
    await AsyncStorage.setItem('apptheme', theme);
  }, []);

  useEffect(() => {
    const load = async () => {
      const stored = await AsyncStorage.getItem('apptheme');
      if (stored) {
        await changeTheme(stored);
      }
    };
    load();
  }, [changeTheme]);

  const toggleTheme = async () => {
    const newTheme = isDark ? 'light' : 'dark';
    await changeTheme(newTheme);
  };

  return (
    <KeyboardAvoidingView
      style={[{ flex: 1 }, { backgroundColor: colors.background }]}
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      keyboardVerticalOffset={Platform.OS === 'ios' ? 60 : 0}
    >
      <ThemeContext.Provider value={{ colors, isDark, toggleTheme }}>
        {children}
      </ThemeContext.Provider>
    </KeyboardAvoidingView>
  );
};


export const useThemeToggle  = () => {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme must be used within a ThemeProvider');
  }
  return context;
};

export const useTheme = (): ThemeContextProps => useContext(ThemeContext);
