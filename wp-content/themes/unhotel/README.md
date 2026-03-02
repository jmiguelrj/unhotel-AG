# Unhotel Theme - Gulp Build Setup

## Installation

1. Install Node.js dependencies:
```bash
npm install
```

## Usage

### Development Mode (with watch and BrowserSync):
```bash
npm start
# or
gulp
```

This will:
- Compile Sass to CSS
- Bundle and minify JavaScript
- Watch for file changes
- Auto-reload browser via BrowserSync

### Build for Production:
```bash
npm run build
# or
gulp build
```

This will compile and minify all assets without watch mode.

### Watch Files Only (no BrowserSync):
```bash
npm run watch
# or
gulp watch
```

## Project Structure

```
unhotel/
├── assets/
│   ├── src/              # Source files (editable)
│   │   ├── scss/
│   │   │   └── style.scss
│   │   └── js/
│   │       └── main.js
│   └── dist/             # Compiled files (auto-generated, don't edit)
│       ├── css/
│       │   ├── style.css
│       │   ├── style.min.css
│       │   └── *.map (sourcemaps)
│       └── js/
│           ├── main.js
│           ├── main.min.js
│           └── *.map (sourcemaps)
├── gulpfile.js
├── package.json
└── style.css             # WordPress theme header only
```

## Configuration

### Update BrowserSync URL
Edit `gulpfile.js` and change the `localURL` variable to match your local development URL:

```javascript
const localURL = 'http://localhost:3100'; // Change this
```

## Notes

- The `assets/dist/` folder is auto-generated and should not be edited manually
- Source files are in `assets/src/`
- WordPress still requires `style.css` in the theme root for theme recognition
- The actual styles are loaded from `assets/dist/css/style.min.css`
