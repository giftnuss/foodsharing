module.exports = {
  root: true,
  parser: '@typescript-eslint/parser',
  plugins: [
    '@typescript-eslint',
  ],
  extends: 'standard-with-typescript',
  parserOptions: {
    "project": "./tsconfig.json"
  },
  rules: {
    "@typescript-eslint/semi": ["error", "always"], // implicit semicolons can cause confusion, as not every new line is a semicolon, but some are
    "@typescript-eslint/indent": ["error", 4], // larger indentations make the code easier to oversee, and in object-oriented languages, you shouldn't indent so deeply. That's why we also use indentations of 4 for our PHP code
    "@typescript-eslint/strict-boolean-expressions": "off" // explicit boolean comparisons are good, however, things like `const x = y || 'default';` are easier to read and to understand than checking for thousand possibilities of y being null/undefined/NaN/whatever JS offers
  }
};
