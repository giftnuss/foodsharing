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
  env: {
    "node": true,
  },
  rules: {
    "@typescript-eslint/semi": ["error", "always"], // implicit semicolons can cause confusion, as not every new line is a semicolon, but some are
    "@typescript-eslint/indent": ["error", 4], // larger indentations make the code easier to oversee, and in object-oriented languages, you shouldn't indent so deeply. That's why we also use indentations of 4 for our PHP code
    "@typescript-eslint/strict-boolean-expressions": "off", // explicit boolean comparisons are good, but JavaScript is not made for this. If you always have to list all possibilities of something being null/undefined/NaN/whatever JS offers, you're more likely to produce errors and you code becomes less readable
  }
};
