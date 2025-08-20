import React, { useState } from 'react';
import { View, TextInput, FlatList, Text, TouchableOpacity, StyleSheet } from 'react-native';
import { useTheme } from '../context/ThemeContext';
import { shadowBox } from "./../utils/colors";

interface Option {
  label: string;
  value: string;
}

interface InputAutocompleteProps {
  options: Option[];
  onSelect: (value: string) => void;
  placeholder?: string;
  initialValue?: string;
}


export default function InputAutocomplete({ options, onSelect, placeholder, initialValue = '' }: InputAutocompleteProps) {
  const { colors } = useTheme();
  const [query, setQuery] = useState('');
  const [filtered, setFiltered] = useState<Option[]>([]);
  
  React.useEffect(() => {
    const matched = options.find((o) => o.value === initialValue);
    if (matched) {
      setQuery(matched.label); // ← synchroniser avec le label
    }
  }, [initialValue, options]);


  const handleChange = (text: string) => {
    setQuery(text);
    setFiltered(
      options.filter(item => item.label.toLowerCase().includes(text.toLowerCase()))
    );
  };

  const handleSelect = (item: Option) => {
    setQuery(item.label);
    setFiltered([]);
    onSelect(item.value);
  };

  return (
    <View style={styles.container}>
      <TextInput
        value={query}
        placeholder={placeholder || 'Tapez ou sélectionnez...'}
        onChangeText={handleChange}
        style={[
          styles.input,
          {
            backgroundColor: colors.surface,
            color: colors.text,
            borderColor: colors.surface,
          },
          shadowBox
        ]}
        placeholderTextColor={colors.placeholder}
      />
      {filtered.length > 0 && (
        <FlatList
          nestedScrollEnabled={true}
          data={filtered}
          keyExtractor={(item) => item.value}
          style={[styles.list, { backgroundColor: colors.surface }]}
          renderItem={({ item }) => (
            <TouchableOpacity onPress={() => handleSelect(item)} style={styles.item}>
              <Text style={{ color: colors.text }}>{item.label}</Text>
            </TouchableOpacity>
          )}
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    marginTop: 25,
    position: 'relative',
  },
  input: {
    height: 48,
    borderRadius: 12,
    borderWidth: 1,
    paddingHorizontal: 16,
    fontSize: 16,
  },
  list: {
    position: 'absolute',
    top: 50,
    left: 0,
    right: 0,
    zIndex: 10,
    borderRadius: 8,
    borderWidth: 1,
    maxHeight: 150,
  },
  item: {
    padding: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#ccc',
  },
});
