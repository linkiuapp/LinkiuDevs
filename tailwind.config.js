/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    // Asegúrate de que Tailwind escanee tus archivos Blade y otros donde uses clases
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    // Feature-based Architecture - Incluir archivos Blade en Features y Shared
    './app/Features/**/*.blade.php',
    './app/Shared/**/*.blade.php',
  ],
  theme: {
    extend: {
      // Tipografías personalizadas
      fontFamily: {
        'heading': ['Inter', 'sans-serif'],
        'body': ['Inter', 'sans-serif'],
        'sans': ['Inter', 'sans-serif'], // Por defecto Inter
      },


      // Escala tipográfica optimizada
      fontSize: {
        'xs': ['10px', { lineHeight: '12px', letterSpacing: '0.025em' }],
        'sm': ['12px', { lineHeight: '14px', letterSpacing: '0.025em' }],
        '2xs': ['14px', { lineHeight: '16px', letterSpacing: '0.025em' }],
        'base': ['16px', { lineHeight: '18px', letterSpacing: '0.025em' }],
        'basex': ['20px', { lineHeight: '22px', letterSpacing: '0.025em' }],
        'lg': ['24px', { lineHeight: '26px', letterSpacing: '0.025em' }],
        'xl': ['32px', { lineHeight: '34px', letterSpacing: '0.025em' }],
        '2xl': ['40px', { lineHeight: '42px', letterSpacing: '0.025em' }],
        '3xl': ['48px', { lineHeight: '40px', letterSpacing: '0.025em' }],
        '4xl': ['56px', { lineHeight: '58px', letterSpacing: '0.025em' }],
        '5xl': ['64px', { lineHeight: '66px', letterSpacing: '0.025em' }],
        '6xl': ['72px', { lineHeight: '74px', letterSpacing: '0.025em' }],


        // Tipografía nueva
        'h1': ['72px', { lineHeight: '120%', letterSpacing: '0.025em' }],
        'h2': ['64px', { lineHeight: '120%', letterSpacing: '0.025em' }],
        'h3': ['56px', { lineHeight: '120%', letterSpacing: '0.025em' }],
        'h4': ['48px', { lineHeight: '120%', letterSpacing: '0.025em' }],
        'h5': ['40px', { lineHeight: '120%', letterSpacing: '0.025em' }],
        'h6': ['32px', { lineHeight: '120%', letterSpacing: '0.025em' }],
        'h7': ['24px', { lineHeight: '120%', letterSpacing: '0.025em' }],
        'body-large': ['20px', { lineHeight: '120%', letterSpacing: '0.025em' }],
        'body-regular': ['16px', { lineHeight: '120%', letterSpacing: '0.025em' }],
        'body-small': ['14px', { lineHeight: '120%', letterSpacing: '0.025em' }],
        'caption': ['12px', { lineHeight: '120%', letterSpacing: '0.025em' }],
        'small': ['10px', { lineHeight: '120%', letterSpacing: '0.025em' }],
      },
      // Pesos tipográficos
      fontWeight: {
        'light': '300',
        'normal': '400',
        'semibold': '600',
        'bold': '700',
        'black': '900',

        // Pesos tipográficos nuevos
        'light': '300',
        'regular': '400',
        'medium': '600',
        'bold': '700',
        'extrabold': '800',
      },
      colors: {

        primary: {
        50: '#fbe9f6',
        75: '#f0a6db',
        100: '#ea82cc',
        200: '#e04cb6',
        300: '#da27a7',
        400: '#991b75',
        500: '#851866',
      },
      secondary: {
        50: '#e6e8ed',
        75: '#96a2b4',
        100: '#6b7b95',
        200: '#2b4267',
        300: '#001b48',
        400: '#001332',
        500: '#00102c',
      },

        // Paleta Error (Rojo nuevo)

        error: {
        50: '#fdeaec',
        75: '#f8a9b3',
        100: '#f58693',
        200: '#f05265',
        300: '#ed2e45',
        400: '#a62030',
        500: '#911c2a',
      },

      // Paleta Info (Azul nuevo)
      info: {
        50: '#e6e6ff',
        75: '#9696ff',
        100: '#6b6bfe',
        200: '#2b2bfe',
        300: '#0000fe',
        400: '#0000b2',
        500: '#00009b',
      },

      // Paleta Success (Verde nuevo)
      success: {
        50: '#e6f9f1',
        75: '#96e8c4',
        100: '#6bdfab',
        200: '#2bd187',
        300: '#00c76f',
        400: '#008b4e',
        500: '#007944',
      },

      // Paleta Warning (Amarillo nuevo)
      warning: {
        50: '#fff7e7',
        75: '#ffdd9c',
        100: '#ffcf73',
        200: '#ffbb36',
        300: '#ffad0d',
        400: '#b37909',
        500: '#9c6a08',
      },

      // Paleta Texto (Negro nuevo)
      black: {
        50: '#e8e8e9',
        75: '#a2a2a3',
        100: '#7b7b7d',
        200: '#434344',
        300: '#1c1c1e',
        400: '#141415',
        500: '#111112',
      },

      // Paleta Disabled (Grises nuevos)
      disabled: {
        50: '#fdfdfd',
        75: '#f8f8f8',
        100: '#f6f6f6',
        200: '#f2f2f2',
        300: '#efefef',
        400: '#a7a7a7',
        500: '#929292',
      },

      // Paleta White (Grises claros/Blancos)
      white: {
        50: '#FBFDFF',
        100: '#EFF6FD',
        200: '#E8F2FC',
        300: '#DEECFB',
        400: '#D7E8FA',
        600: '#3A4550',
      },

      // Paleta Accent (Blanco nuevo)
      accent: {
        50: '#fdfdff',
        75: '#f6f5fd',
        100: '#f2f1fd',
        200: '#eceafc',
        300: '#e8e6fb',
        400: '#a2a1b0',
        500: '#8e8c99',
      },

      // Paleta Gray (Alias que combina white/black según reglas de diseño)
      gray: {
        50: '#F0F0F0',   // Del sistema black
        100: '#EFF6FD',  // Del sistema white
        200: '#E8F2FC',  // Del sistema white
        300: '#DEECFB',  // Del sistema white
        400: '#7b7b7d',  // Del sistema black
        500: '#434344',  // Del sistema black
        600: '#3A4550',  // Del sistema white
        700: '#1c1c1e',  // Del sistema black
        800: '#141415',  // Del sistema black
        900: '#111112',  // Del sistema black
      },



      },
    },
  },
  plugins: [],
  darkMode: 'class', // Asumo que usas dark mode basado en tus clases `dark:bg-neutral-600`
}