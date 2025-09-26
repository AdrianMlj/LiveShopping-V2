import React from 'react';
import RNPickerSelect from 'react-native-picker-select';
import { useTranslation } from 'react-i18next';
import { useTheme } from '../../context/ThemeContext';

const LanguageDropdown = () => {
  const { i18n } = useTranslation();
  const { colors } = useTheme();


  const handleLanguageChange = (lang: string) => {
    if (lang) {
      i18n.changeLanguage(lang);
    }
  };

  return (
    <RNPickerSelect
      onValueChange={handleLanguageChange}
      value={i18n.language}
      placeholder={{ label: 'Select a language', value: null }}
      items={[
        { label: '🇫🇷 Français', value: 'fr' },
        { label: '🇬🇧 English', value: 'en' },
      ]}
      style={{
        inputIOS: {
          height: 10,
          fontSize: 16,
          paddingVertical: 12,
          paddingHorizontal: 10,
          borderWidth: 1,
          borderColor: colors.border,
          borderRadius: 8,
          color: colors.text,
          paddingRight: 30,
          backgroundColor: colors.surface,
        },
        inputAndroid: {
          height: 30,
          fontSize: 16,
          paddingHorizontal: 10,
          paddingVertical: 8,
          borderWidth: 0.5,
          borderColor: colors.border,
          borderRadius: 8,
          color: colors.text,
          backgroundColor: colors.surface,
        },
      }}
    />
  );
};

export default LanguageDropdown;
