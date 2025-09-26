import React, { useState } from 'react';
import { useTheme } from '../../context/ThemeContext';
import { View, Text, TouchableOpacity, StyleSheet, LayoutAnimation, Platform, UIManager } from 'react-native';
import { shadowBox } from './../../utils/colors';

type Props = {
  title: string;
  children: React.ReactNode;
};

if (Platform.OS === 'android' && UIManager.setLayoutAnimationEnabledExperimental) {
  UIManager.setLayoutAnimationEnabledExperimental(true);
}

export default function DropdownParameters({ title, children }: Props) {
  const { colors } = useTheme();
  const [open, setOpen] = useState(false);

  const toggleDropdown = () => {
    LayoutAnimation.configureNext(LayoutAnimation.Presets.easeInEaseOut);
    setOpen(!open);
  };

  return (
    <View style={[styles.dropdown, { backgroundColor: colors.surface, ...shadowBox }]}>
      <TouchableOpacity onPress={toggleDropdown} style={[styles.header, { backgroundColor: colors.surface }]}>
        <Text style={[styles.headerText, {color: colors.text}]}>{title}</Text>
        <Text style={[styles.arrow, {color: colors.text}]}>{open ? '▲' : '▼'}</Text>
      </TouchableOpacity>
      {open && <View>{children}</View>}
    </View>
  );
}

const styles = StyleSheet.create({
  dropdown: {
    marginTop: 20,
    width: '95%',
    marginHorizontal: '2.5%',
    borderRadius: 15,
    overflow: 'hidden',
  },
  header: {
    padding: 20,
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  headerText: {
    fontSize: 18,
    fontWeight: 'bold',
  },
  arrow: {
    fontSize: 18,
  },
  content: {
    padding: 20,
  },
});
