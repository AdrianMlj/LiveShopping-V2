import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import i18n from '../config/i18n';

interface LanguageContextProps {
  language: string;
  changeLanguage: (lang: string) => void;
}

const LanguageContext = createContext<LanguageContextProps | undefined>(undefined);

export const LanguageProvider = ({ children }: { children: ReactNode }) => {
  const [language, setLanguage] = useState('fr');

  useEffect(() => {
    const load = async () => {
      const stored = await AsyncStorage.getItem('appLanguage');
      if (stored) {
        setLanguage(stored);
        await i18n.changeLanguage(stored);
      }
    };
    load();
  }, []);

  const changeLanguage = async (lang: string) => {
    setLanguage(lang);
    await i18n.changeLanguage(lang);
    await AsyncStorage.setItem('appLanguage', lang);
  };

  return (
    <LanguageContext.Provider value={{ language, changeLanguage }}>
      {children}
    </LanguageContext.Provider>
  );
};

export const useLanguage = () => {
  const context = useContext(LanguageContext);
  if (!context) throw new Error('useLanguage must be used inside LanguageProvider');
  return context;
};
