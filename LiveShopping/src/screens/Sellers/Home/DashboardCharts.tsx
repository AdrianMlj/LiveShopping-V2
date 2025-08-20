import React, { useEffect, useState } from 'react';
import { Dimensions, ScrollView, Text, ActivityIndicator } from 'react-native';
import { BarChart, LineChart, PieChart } from 'react-native-gifted-charts';
import { getDashboardData } from '../../../services/Admin/Dashboard';
import { useUser } from '../../../context/AuthContext';

const { width } = Dimensions.get('window');

export default function Charts() {
  const { user } = useUser();
  const [statsMensuelles, setStatsMensuelles] = useState<{ labels: string[]; chiffre_affaires: number[] }>({ labels: [], chiffre_affaires: [] });
  const [ventesParCategorieParMois, setVentesParCategorieParMois] = useState<{ labels: string[]; datasets: Record<string, number[]> }>({ labels: [], datasets: {} });
  const [ventesParArticle, setVentesParArticle] = useState<{ datasets: Record<string, number[]> }>({ datasets: {} });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    (async () => {
      try {
        const result = await getDashboardData(user?.id_user);
        if (result) {
          setStatsMensuelles(result.stats);
          setVentesParCategorieParMois(result.ventesParCategorieParMois);
          setVentesParArticle(result.ventesParArticle);
        }
      } catch (err) {
        console.error(err);
      } finally {
        setLoading(false);
      }
    })();
  }, [user]);

  if (!user) return null;

  if (loading) {
    return <ActivityIndicator size="large" style={{ marginTop: 50 }} />;
  }

  const barData = statsMensuelles.chiffre_affaires.map((val, i) => ({
    value: val,
    label: statsMensuelles.labels[i]
  }));

  const lineDataSets = Object.entries(ventesParCategorieParMois.datasets).map(([, arr], idx) => ({
    data: arr.map((val, i) => ({ value: val, label: ventesParCategorieParMois.labels[i] })),
    lineColor: ['#567AF4', '#FF6384', '#4BC0C0'][idx % 3]
  }));

  const pieData = Object.entries(ventesParArticle.datasets).map(([label, arr]) => ({
    value: arr.reduce((a, b) => a + b, 0),
    label
  }));

  return (
    <ScrollView style={{ padding: 16 }}>
      <Text style={{ fontSize: 16, fontWeight: 'bold', marginBottom: 8 }}>Chiffre d’affaires mensuel</Text>
      <BarChart
        data={barData}
        width={width - 32}
        height={200}
        color="#567AF4"
        spacing={0.3}
        roundedTop
      />

      <Text style={{ marginTop: 24, fontSize: 16, fontWeight: 'bold' }}>Ventes par catégorie</Text>
      {lineDataSets.map((set, idx) => (
        <LineChart
          key={idx}
          data={set.data}
          width={width - 32}
          height={180}
          color={set.lineColor}
          areaChart
          spacing={30}
        />
      ))}

      <Text style={{ marginTop: 24, fontSize: 16, fontWeight: 'bold' }}>Répartition des articles</Text>
      <PieChart
        data={pieData}
        donut
        showText
      />
    </ScrollView>
  );
}
