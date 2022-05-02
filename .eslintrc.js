module.exports = {
    "env": {
        "browser": true,
        "es2021": true
    },
    "extends": "eslint:recommended",
    "parserOptions": {
        "ecmaVersion": "latest",
        "sourceType": "module"
    },
    "rules": {
        "no-duplicate-imports": "error",
        "block-scoped-var": "error",
        "guard-for-in": "error",
        "no-else-return": "error",
        "eqeqeq": "error", // strict comparisons
        "no-implicit-globals": "error",
        "no-unused-vars": ["error", { "argsIgnorePattern": "^vnode$" }],
        "no-var": "error",
        "prefer-const": "warn",
    }
}
