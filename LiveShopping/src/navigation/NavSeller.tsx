import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import HomeScreen from '../screens/Sellers/Home/HomeScreen';
import BagScreen from '../screens/Client/bags/BagScreen';
import LiveScreen from '../screens/Sellers/Live/LiveScreen';
import FavoriteScreen from '../screens/Client/Favorites/FavoriteScreen';
import ProfileScreen from '../screens/Client/Profile/ProfileScreen';
import { shadowBox } from './../utils/colors';
import { View, Image, Text, StyleSheet } from 'react-native';
import { useTheme } from '../context/ThemeContext';

const Tab = createBottomTabNavigator();

const NavSeller = () => {
  const { colors } = useTheme();
  return (
    <Tab.Navigator
      screenOptions={{
        tabBarShowLabel: false,
        tabBarActiveTintColor: colors.primary,
        tabBarInactiveTintColor: colors.placeholder,
        tabBarStyle: {
          position: 'absolute',
          bottom: 60,
          height: 50,
          width: '90%',
          backgroundColor: colors.surface,
          borderRadius: 15,
          elevation: 0,
          marginHorizontal: '5%',
          ...shadowBox,
        },
      }}
    >
      <Tab.Screen
        name="HomeSeller"
        component={HomeScreen}
        options={{
          headerShown: false,
          tabBarIcon: ({ focused }) => (
            <View
              style={{
                alignItems: 'center',
                justifyContent: 'center',
                top: 5, // tu peux ajuster ou enlever pour centrer verticalement
              }}
            >
              <Image
                source={
                  focused
                    ? require('../assets/icons/Home.png')
                    : require('../assets/icons/Home.png')
                }
                resizeMode="contain"
                style={{
                  width: 25,
                  height: 25,
                  tintColor: focused ? '#4968F4' : '#999999',
                }}
              />
            </View>
          ),
        }}
      />
      <Tab.Screen
        name="BagSeller"
        component={BagScreen}
        options={{
          headerShown: false,
          tabBarIcon: ({ focused }) => (
            <View
              style={{
                alignItems: 'center',
                justifyContent: 'center',
                top: 5, // tu peux ajuster ou enlever pour centrer verticalement
              }}
            >
              <Image
                source={
                  focused
                    ? require('../assets/icons/histo-sale.png')
                    : require('../assets/icons/histo-sale.png')
                }
                resizeMode="contain"
                style={{
                  width: 25,
                  height: 25,
                  tintColor: focused ? '#4968F4' : '#999999',
                }}
              />
            </View>
          ),
        }}
      />
      <Tab.Screen
        name="SearchSeller"
        component={LiveScreen}
        options={{
          headerShown: false,
          tabBarIcon: ({ focused }) => (
            <View
              style={{
                alignItems: 'center',
                justifyContent: 'center',
                top: -20,
                backgroundColor: focused ? colors.logout : colors.error,
                borderRadius: 35,
                width: 70,
                height: 70,
                shadowColor: '#000',
                shadowOffset: { width: 0, height: 5 },
                shadowOpacity: 0.15,
                shadowRadius: 5,
                elevation: 6,
              }}
            >
              <Text style={[styles.title, {color: "white"}]}>Live</Text>
            </View>
          ),
        }}
      />
      <Tab.Screen
        name="FavoritesSeller"
        component={FavoriteScreen}
        options={{
          headerShown: false,
          tabBarIcon: ({ focused }) => (
            <View
              style={{
                alignItems: 'center',
                justifyContent: 'center',
                top: 5, // tu peux ajuster ou enlever pour centrer verticalement
              }}
            >
              <Image
                source={
                  focused
                    ? require('../assets/icons/Bag.png')
                    : require('../assets/icons/Bag.png')
                }
                resizeMode="contain"
                style={{
                  width: 25,
                  height: 25,
                  tintColor: focused ? '#4968F4' : '#999999',
                }}
              />
            </View>
          ),
        }}
      />
      <Tab.Screen
        name="ProfileSeller"
        component={ProfileScreen}
        options={{
          headerShown: false,
          tabBarIcon: ({ focused }) => (
            <View
              style={{
                alignItems: 'center',
                justifyContent: 'center',
                top: 5, // tu peux ajuster ou enlever pour centrer verticalement
              }}
            >
              <Image
                source={
                  focused
                    ? require('../assets/icons/Menu.png')
                    : require('../assets/icons/Menu.png')
                }
                resizeMode="contain"
                style={{
                  width: 25,
                  height: 25,
                  tintColor: focused ? '#4968F4' : '#999999',
                }}
              />
            </View>
          ),
        }}
      />
    </Tab.Navigator>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  title: { fontSize: 24, fontWeight: 'bold' },
});

export default NavSeller;
