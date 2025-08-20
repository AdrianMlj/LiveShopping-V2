import { Platform } from "react-native";

export type ThemeColors = {
  readonly background: string;
  readonly surface: string;
  readonly primary: string;
  readonly text: string;
  readonly textLink: string;
  readonly placeholder: string;
  readonly border: string;
  readonly error: string;
  readonly logout: string;
};

export const shadowBox = Platform.select({
  ios: {
    shadowColor: "rgba(0, 0, 0, 0.45)",
    shadowOffset: { width: 0, height: -5 },
    shadowOpacity: 0.25,
    shadowRadius: 10,
  },
  android: {
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


export const Colors = {
  light: {
    background: "#FFFFFF",
    surface: "#F2F2F2",
    primary: "#4968F4",
    text: "#11181C",
    textLink: "#4968F4",
    placeholder: "#999999",
    border: "#E0E0E0",
    error: "#DC2626",
    logout: "#ff0000ff",
  } as const satisfies ThemeColors,

  dark: {
    background: "#121212",
    surface: "#1E1E1E",
    primary: "#4D85FF",
    text: "#ECECEE",
    textLink: "#4968F4",
    placeholder: "#AAAAAA",
    border: "#333333",
    error: "#FF6B6B",
    logout: "#ff0000ff",
  } as const satisfies ThemeColors,
};
