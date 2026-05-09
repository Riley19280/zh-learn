import js from '@eslint/js'
import stylistic from '@stylistic/eslint-plugin'
import importPlugin from 'eslint-plugin-import'
import importNewlinesPlugin from 'eslint-plugin-import-newlines'
import reactHooks from 'eslint-plugin-react-hooks'
import globals from 'globals'
import typescript from 'typescript-eslint'

/** @type {import('eslint').Linter.Config[]} */
export default [
  js.configs.recommended,
  ...typescript.configs.recommended,
  importPlugin.flatConfigs.recommended,
  stylistic.configs.recommended,
  {
    languageOptions: {
      globals: {
        ...globals.browser,
      },
    },
    rules: {
      '@stylistic/react-in-jsx-scope': 'off',
      '@stylistic/prop-types': 'off',
      '@stylistic/no-unescaped-entities': 'off',
      '@stylistic/comma-dangle': [
        'error',
        {
          arrays: 'always-multiline',
          objects: 'always-multiline',
          imports: 'always-multiline',
          exports: 'always-multiline',
          functions: 'always-multiline',
          importAttributes: 'always-multiline',
          dynamicImports: 'always-multiline',
        },
      ],
      '@stylistic/semi': 'error',
      '@stylistic/brace-style': ['error', '1tbs'],
    },
    settings: {
      'react': {
        version: 'detect',
      },
      'import/resolver': {
        typescript: true,
        node: true,
      },
    },
  },
  {
    plugins: {
      'react-hooks': reactHooks,
      'import-newlines': importNewlinesPlugin,
      '@stylistic': stylistic,
    },
    rules: {
      'react-hooks/rules-of-hooks': 'error',
      'react-hooks/exhaustive-deps': 'warn',
      'jsx-quotes': ['error', 'prefer-double'],
      '@typescript-eslint/no-unused-vars': [
        'warn',
        {
          argsIgnorePattern: '^_',
          varsIgnorePattern: '^_',
        },
      ],
      '@typescript-eslint/no-explicit-any': 'off',
      'no-shadow': 'off',
      'max-len': 'off',
      'import-newlines/enforce': [
        'error',
        {
          items: 0,
        },
      ],
      'import/order': [
        'error',
        {
          alphabetize: {
            order: 'asc',
            caseInsensitive: true,
          },
        },
      ],
      'import/first': 'error',
      'import/no-unresolved': 'off',
    },
  },
  {
    ignores: [
      'vendor',
      'node_modules',
      'public',
      'bootstrap/ssr',
      'tailwind.config.js',
      '.venv',
      'extension',
    ],
  },
]
