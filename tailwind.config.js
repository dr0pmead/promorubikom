/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './*.php',
    './**/*.php',
    './template-parts/**/*.php',
    './assets/js/**/*.js',
  ],
  theme: {
    extend: {
      fontFamily: {
        proxima: ['"Proxima Nova"', 'sans-serif'],
      },
    },
  },
  plugins: [],
}

