import React from 'react';
import { TextInput, StyleSheet, TextInputProps } from 'react-native';
import { useTheme } from '../context/ThemeContext';

interface InputFieldProps extends TextInputProps {}

export default function InputField(props: InputFieldProps) {
  const { colors } = useTheme();
  return <TextInput style={[styles.input, { color: colors.text }, { backgroundColor: colors.surface }, { borderColor: colors.border }]} placeholderTextColor={colors.placeholder} {...props} />;
}

const styles = StyleSheet.create({
  input: {
    marginTop: 25,
    height: 48,
    borderRadius: 12,
    paddingHorizontal: 16,
    paddingLeft: 20,
    fontSize: 16,
    shadowColor: "rgba(0, 0, 0, 0.45)",
    shadowOffset: {
      width: 0,
      height: -5,
    },
    shadowOpacity: 0.25,
    shadowRadius: 10,
    elevation: 10,
  },
});    