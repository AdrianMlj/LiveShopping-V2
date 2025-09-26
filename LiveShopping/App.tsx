import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import SplashScreen from './src/splash/index';
import SignInScreen from './src/screens/Auth/SignInScreen';
import SignUpScreen from './src/screens/Auth/SignUpScreen';
import AppClient from './src/screens/Client/App';
import AppSeller from './src/screens/Sellers/App';
import { UserProvider, useUser } from './src/context/AuthContext';
import { ThemeProvider } from './src/context/ThemeContext';
import { LanguageProvider } from './src/context/LanguageContext';
import ProfileViewScreen from './src/screens/Client/Profile/ProfileViewScreen';
import ProfileEditInline from './src/screens/Client/Profile/ProfileEditScreen';

const Stack = createNativeStackNavigator();

const MainNavigator = () => {
  const { user } = useUser();

  return (
    <NavigationContainer>
      <Stack.Navigator>
        {user ? (
        <>
          {user.is_seller ? (
            <Stack.Screen
              name="SellerHome"
              component={AppSeller}
              options={{ headerShown: false }}
            />
          ) : (
            <Stack.Screen
              name="ClientHome"
              component={AppClient}
              options={{ headerShown: false }}
            />
          )}
        </>
      ) : (
        <Stack.Screen
          name="signin"
          component={SignInScreen}
          options={{ headerShown: false }}
        />
      )}

        <Stack.Screen name="Splash" component={SplashScreen} options={{ headerShown: false }} />
        <Stack.Screen name="profileView" component={ProfileViewScreen} options={{ headerShown: false }} />
        <Stack.Screen name="profileEdit" component={ProfileEditInline} options={{ headerShown: false }} />
        <Stack.Screen name="signup" component={SignUpScreen} options={{ headerShown: false }} />
        <Stack.Screen name="client" component={AppClient} options={{ headerShown: false }} />
        <Stack.Screen name="seller" component={AppSeller} options={{ headerShown: false }} />
      </Stack.Navigator>
    </NavigationContainer>
  );
};

export default function App() {
  return (
    <UserProvider>
      <LanguageProvider>
        <ThemeProvider>
          <MainNavigator />
        </ThemeProvider>
      </LanguageProvider>
    </UserProvider>
  );
}
