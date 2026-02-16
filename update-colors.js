// Color replacement script
// Run with: node update-colors.js

const fs = require('fs');
const path = require('path');

const colorMap = {
  // Blue to Green mappings
  'blue-50': 'green-50',
  'blue-100': 'green-100',
  'blue-200': 'green-200',
  'blue-300': 'green-300',
  'blue-400': 'green-400',
  'blue-500': 'green-500',
  'blue-600': 'green-600',
  'blue-700': 'green-700',
  'blue-800': 'green-800',
  'blue-900': 'green-900',
  
  // Indigo to darker green
  'indigo-50': 'green-50',
  'indigo-100': 'green-100',
  'indigo-200': 'green-200',
  'indigo-300': 'green-300',
  'indigo-400': 'green-400',
  'indigo-500': 'green-600',
  'indigo-600': 'green-700',
  'indigo-700': 'green-800',
  'indigo-800': 'green-900',
  'indigo-900': 'green-950',
};

function replaceColorsInFile(filePath) {
  let content = fs.readFileSync(filePath, 'utf8');
  let modified = false;
  
  for (const [oldColor, newColor] of Object.entries(colorMap)) {
    const regex = new RegExp(oldColor, 'g');
    if (content.includes(oldColor)) {
      content = content.replace(regex, newColor);
      modified = true;
    }
  }
  
  if (modified) {
    fs.writeFileSync(filePath, content, 'utf8');
    console.log(`✓ Updated: ${filePath}`);
  }
}

function walkDir(dir) {
  const files = fs.readdirSync(dir);
  
  files.forEach(file => {
    const filePath = path.join(dir, file);
    const stat = fs.statSync(filePath);
    
    if (stat.isDirectory()) {
      if (!file.startsWith('.') && file !== 'node_modules') {
        walkDir(filePath);
      }
    } else if (file.endsWith('.jsx') || file.endsWith('.js') || file.endsWith('.tsx') || file.endsWith('.ts')) {
      replaceColorsInFile(filePath);
    }
  });
}

console.log('Starting color replacement: Blue → Green\n');
walkDir('./frontend/src');
console.log('\n✓ Color replacement complete!');
