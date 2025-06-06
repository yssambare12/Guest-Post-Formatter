/**
 * Form Field Component
 * 
 * A reusable component for form fields with validation.
 */

import React from 'react';

const FormField = ({
    type,
    id,
    name,
    label,
    required,
    value,
    onChange,
    placeholder,
    description,
    error
}) => {
    const inputClasses = `gpfs-input gpfs-focus-visible ${error ? 'border-red-300' : ''}`;
    const textareaClasses = `gpfs-textarea gpfs-focus-visible ${error ? 'border-red-300' : ''}`;
    
    return (
        <div className="gpfs-form-row">
            <label htmlFor={id} className="gpfs-label">
                {label} {required && <span className="text-red-500">*</span>}
            </label>
            
            {type === 'textarea' ? (
                <textarea
                    id={id}
                    name={name}
                    className={textareaClasses}
                    value={value}
                    onChange={onChange}
                    placeholder={placeholder}
                    required={required}
                    aria-required={required ? 'true' : 'false'}
                    aria-invalid={error ? 'true' : 'false'}
                    aria-describedby={error ? `${id}_error` : description ? `${id}_desc` : undefined}
                    rows="4"
                ></textarea>
            ) : (
                <input
                    type={type}
                    id={id}
                    name={name}
                    className={inputClasses}
                    value={value}
                    onChange={onChange}
                    placeholder={placeholder}
                    required={required}
                    aria-required={required ? 'true' : 'false'}
                    aria-invalid={error ? 'true' : 'false'}
                    aria-describedby={error ? `${id}_error` : description ? `${id}_desc` : undefined}
                />
            )}
            
            {description && (
                <p id={`${id}_desc`} className="mt-1 text-sm text-gray-500">
                    {description}
                </p>
            )}
            
            {error && (
                <p id={`${id}_error`} className="gpfs-error" role="alert">{error}</p>
            )}
        </div>
    );
};

export default FormField;
