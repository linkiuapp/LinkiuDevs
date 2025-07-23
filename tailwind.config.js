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
      },
      // Pesos tipográficos
      fontWeight: {
        'light': '300',
        'normal': '400',
        'semibold': '600',
        'bold': '700',
        'black': '900',
      },
      colors: {
        // Paleta Primary (Violeta)
        primary: {
          50: '#F1EAFF',
          100: '#B48FFF',
          200: '#7432F8',
          300: '#350692',
        },
        // Paleta Secondary (Naranja)
        secondary: {
          50: '#FFF0E6',
          100: '#FFAD75',
          200: '#FD6905',
          300: '#973D00',
        },
        // Paleta Success (Verde)
        success: {
          50: '#E5FFF4',
          100: '#73FFC2',
          200: '#00DF7F',
          300: '#007945',
        },
        // Paleta Error (Rojo)
        error: {
          50: '#FFE7E9',
          100: '#FF7B85',
          200: '#F60F21',
          300: '#90000B',
        },
        // Paleta Warning (Amarillo/Gris)
        warning: {
          50: '#FFF9E5',
          100: '#FFDE73',
          200: '#FFC300',
          300: '#997500',
        },
        // Paleta Info (Azul)
        info: {
          50: '#E8E9FF',
          100: '#8385FF',
          200: '#191CD9',
          300: '#000273',
        },
        // Paleta White (Grises claros)
        white: {
          50: '#FBFDFF',
          100: '#EFF6FD',
          200: '#E8F2FC',
          300: '#DEECFB',
          400: '#D7E8FA',
        },
        // Paleta Black (Grises oscuros)
        black: {
          50: '#E6E6E7',
          100: '#98989B',
          200: '#6D6D71',
          300: '#2E2E34',
          400: '#03030B',
        },
      },
    },
  },
  plugins: [],
  darkMode: 'class', // Asumo que usas dark mode basado en tus clases `dark:bg-neutral-600`
}