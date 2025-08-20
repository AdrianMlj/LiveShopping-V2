import React, { useEffect, useState } from "react";
import { View, Text, FlatList, StyleSheet, ActivityIndicator } from "react-native";
import { getLiveList } from "../../../services/Live/LiveService";
import type { Live } from "../../../types/Live";

export default function ClientHomeScreen() {
  const [lives, setLives] = useState<Live[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchLives = async () => {
      try {
        const data = await getLiveList(); // Retourne un tableau de Live
        setLives(data);
      } catch (error) {
        console.error("Erreur chargement lives:", error);
      } finally {
        setLoading(false);
      }
    };

    fetchLives();
  }, []);

  if (loading) {
    return (
      <View style={styles.loading}>
        <ActivityIndicator size="large" color="#ff0000" />
      </View>
    );
  }

  return (
    <FlatList
      data={lives}
      keyExtractor={(item) => item.id_live.toString()}
      renderItem={({ item }) => (
        <View style={styles.item}>
          <Text style={styles.title}>Live #{item.id_live}</Text>
          <Text>Début : {item.start_live}</Text>
          {item.end_live && <Text>Fin : {item.end_live}</Text>}
          <Text>Likes : {item.nbr_like ?? 0}</Text>
          <Text>Vendeur : {item.id_seller}</Text>
        </View>
      )}
    />
  );
}

const styles = StyleSheet.create({
  loading: {
    flex: 1,
    justifyContent: "center",
    alignItems: "center",
  },
  item: {
    backgroundColor: "#fff",
    padding: 16,
    marginVertical: 8,
    marginHorizontal: 16,
    borderRadius: 8,
    elevation: 2,
  },
  title: {
    fontSize: 16,
    fontWeight: "bold",
    marginBottom: 4,
  },
});
