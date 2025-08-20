import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import HomeScreen from '../screens/Client/Home/HomeScreen';
import BagScreen from '../screens/Client/bags/BagScreen';
import SearchScreen from '../screens/Client/Search/SearchScreen';
import FavoriteScreen from '../screens/Client/Favorites/FavoriteScreen';
import ProfileScreen from '../screens/Client/Profile/ProfileScreen';
import { shadowBox } from './../utils/colors';
import { View, Image } from 'react-native';
import { useTheme } from '../context/ThemeContext';
// import {
//   StyleSheet,
// } from 'react-native';


const Tab = createBottomTabNavigator();

const NavClient = () => {
  const { colors } = useTheme();
  return (
    <Tab.Navigator
      screenOptions={{
        tabBarShowLabel: false,
        tabBarActiveTintColor: colors.primary,
        tabBarInactiveTintColor: colors.placeholder,
        tabBarStyle: {
          position: 'absolute',
          bottom: 20,
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
        name="HomeClient"
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
        name="BagClient"
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
                    ? require('../assets/icons/Bag.png')
                    : require('../assets/icons/Bag.png')
                }
                resizeMode="contain"
                style={{
                  width: 30,
                  height: 30,
                  tintColor: focused ? '#4968F4' : '#999999',
                }}
              />
            </View>
          ),
        }}
      />
      <Tab.Screen
        name="SearchClient"
        component={SearchScreen}
        options={{
          headerShown: false,
          tabBarIcon: ({ focused }) => (
            <View
              style={{
                alignItems: 'center',
                justifyContent: 'center',
                top: -20,
                backgroundColor: '#4968F4',
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
              <Image
                source={
                  focused
                    ? require('../assets/icons/Search.png')
                    : require('../assets/icons/Search.png')
                }
                resizeMode="contain"
                style={{
                  width: 30,
                  height: 30,
                  tintColor: focused ? '#ffffffff' : '#ffffffff',
                }}
              />
            </View>
          ),
        }}
      />
      <Tab.Screen
        name="FavoritesClient"
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
                    ? require('../assets/icons/Favorites.png')
                    : require('../assets/icons/Favorites.png')
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
        name="ProfileClient"
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

export default NavClient;
