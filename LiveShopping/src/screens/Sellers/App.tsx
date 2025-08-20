import React from 'react';
import {
  NavigationContainer,
  NavigationIndependentTree,
} from '@react-navigation/native';
import NavSeller from './../../navigation/NavSeller';

const AppSeller = () => {
  return (
    <NavigationIndependentTree>
      <NavigationContainer>
        <NavSeller />
      </NavigationContainer>
    </NavigationIndependentTree>
  );
};

export default AppSeller;
