module.exports = {
  "parserOptions": {
    "parser": "babel-eslint",
    "ecmaVersion": 2017
  },
  "extends": ["airbnb-base", "plugin:vue/recommended"],
  "plugins": [
    "import",
    "vue"
  ],
  "env": {
    "browser": true
  },
  "rules": {
    "max-len": 0,
    "no-console": 0,
    "no-param-reassign": 0,
    "prefer-destructuring": 0,
    "no-underscore-dangle": 0,
    "no-plusplus": 0,
    "prefer-template": 0,
    "class-methods-use-this": 0,
    "guard-for-in": 0,
    "no-restricted-syntax": 0,
    "quote-props": 0,
    "vue/max-attributes-per-line": ["error", {
    "singleline": 1,
    "multiline": {
      "max": 1,
      "allowFirstLine": true
    }
    }]
  },
};
