# Postal Mailer WordPress Plugin

A WordPress plugin that provides a step-by-step postal mailing system with a modern, minimalist design.

## Features

- Fixed floating button with notification counter
- Three-step form process:
  1. Recipients list display
  2. Message composition
  3. Cost summary
- Modern, responsive design
- AJAX form submission
- Security features (nonce verification, data sanitization)

## Installation

1. Upload the `postal-mailer` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The postal mailing button will appear on all pages

## Usage

The plugin expects a JavaScript variable called `selectedProperties` to be available in the global scope, containing an array of objects with the following structure:

```javascript
window.selectedProperties = [
    {
        id: "1",
        name: "John Doe",
        address: "123 Main St, City, Country"
    },
    // ... more recipients
];
```

## Security

- All form submissions are protected with WordPress nonces
- Input data is sanitized before processing
- Direct file access is prevented