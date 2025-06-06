/**
 * Guest Post Frontend Submitter - React Components
 * 
 * This file serves as the entry point for the React components used in the plugin.
 */

import React from 'react';
import { createRoot } from 'react-dom/client';
import GuestPostForm from './components/GuestPostForm';

// Initialize the form when the DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Find all form containers
    const formContainers = document.querySelectorAll('.gpfs-react-form-container');
    
    formContainers.forEach(container => {
        // Get data from the container
        const data = JSON.parse(container.dataset.formConfig || '{}');
        
        // Create root and render
        const root = createRoot(container);
        root.render(<GuestPostForm {...data} />);
    });
});
