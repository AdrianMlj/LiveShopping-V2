const { getDefaultConfig, mergeConfig } = require('@react-native/metro-config');
const path = require('path');

const defaultConfig = getDefaultConfig(__dirname);

// Ajoute un alias vide pour éviter l'erreur sur PickerIOS
defaultConfig.resolver.extraNodeModules = {
  ...(defaultConfig.resolver.extraNodeModules || {}),
  './PickerIOS': path.resolve(__dirname, 'emptyModule.js'),
};

module.exports = mergeConfig(defaultConfig, {});
