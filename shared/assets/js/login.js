document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Réinitialiser les messages d'erreur
            document.querySelectorAll('.error-message').forEach(function(el) {
                el.textContent = '';
            });
            
            // Récupérer les valeurs du formulaire
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember') ? document.getElementById('remember').checked : false;
            
            // Valider les données
            let isValid = true;
            
            if (!email) {
                document.getElementById('email').nextElementSibling.textContent = 'L\'adresse email est obligatoire';
                isValid = false;
            } else if (!isValidEmail(email)) {
                document.getElementById('email').nextElementSibling.textContent = 'L\'adresse email est invalide';
                isValid = false;
            }
            
            if (!password) {document.addEventListener('DOMContentLoaded', function() {
                const loginForm = document.getElementById('login-form');
                
                if (loginForm) {
                    loginForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        // Réinitialiser les messages d'erreur
                        document.querySelectorAll('.error-message').forEach(function(el) {
                            el.textContent = '';
                        });
                        
                        // Récupérer les valeurs du formulaire
                        const email = document.getElementById('email').value.trim();
                        const password = document.getElementById('password').value;
                        const remember = document.getElementById('remember') ? document.getElementById('remember').checked : false;
                        
                        // Valider les données
                        let isValid = true;
                        
                        if (!email) {
                            document.getElementById('email').nextElementSibling.textContent = 'L\'adresse email est obligatoire';
                            isValid = false;
                        } else if (!isValidEmail(email)) {
                            document.getElementById('email').nextElementSibling.textContent = 'L\'adresse email est invalide';
                            isValid = false;
                        }
                        
                        if (!password) {
                            document.getElementById('password').nextElementSibling.textContent = 'Le mot de passe est obligatoire';
                            isValid = false;
                        }
                        
                        if (isValid) {
                            // Créer un objet FormData pour envoyer les données
                            const formData = new FormData(loginForm);
                            
                            // Envoyer les données via AJAX
                            fetch(loginForm.action, {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Redirection vers la page appropriée
                                    window.location.href = data.redirect || '/';
                                } else {
                                    // Afficher le message d'erreur
                                    const errorContainer = document.createElement('div');
                                    errorContainer.className = 'error-container';
                                    
                                    const errorMessage = document.createElement('p');
                                    errorMessage.className = 'error';
                                    errorMessage.textContent = data.message;
                                    
                                    errorContainer.appendChild(errorMessage);
                                    
                                    // Insérer le message d'erreur au début du formulaire
                                    const firstChild = loginForm.firstChild;
                                    loginForm.insertBefore(errorContainer, firstChild);
                                    
                                    // Si des erreurs spécifiques sont présentes, les afficher
                                    if (data.data && data.data.errors) {
                                        const errors = data.data.errors;
                                        
                                        for (const field in errors) {
                                            const errorElement = document.getElementById(field).nextElementSibling;
                                            if (errorElement) {
                                                errorElement.textContent = errors[field];
                                            }
                                        }
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Erreur lors de la connexion:', error);
                                
                                // Afficher un message d'erreur générique
                                const errorContainer = document.createElement('div');
                                errorContainer.className = 'error-container';
                                
                                const errorMessage = document.createElement('p');
                                errorMessage.className = 'error';
                                errorMessage.textContent = 'Une erreur est survenue lors de la connexion. Veuillez réessayer.';
                                
                                errorContainer.appendChild(errorMessage);
                                
                                // Insérer le message d'erreur au début du formulaire
                                const firstChild = loginForm.firstChild;
                                loginForm.insertBefore(errorContainer, firstChild);
                            });
                        }
                    });
                }
                
                // Fonction pour valider une adresse email
                function isValidEmail(email) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return emailRegex.test(email);
                }
            });
            
                document.getElementById('password').nextElementSibling.textContent = 'Le mot de passe est obligatoire';
                isValid = false;
            }
            
            // if (isValid) {
            //     // Créer un objet FormData pour envoyer les données
            //     const formData = new FormData(loginForm);
                
            //     // Envoyer les données via AJAX
            //     fetch(loginForm.action, {
            //         method: 'POST',
            //         body: formData
            //     })
            //     .then(response => )
            //     .then(data => {
            //         if (data.success) {
            //             // Redirection vers la page appropriée
            //             // window.location.href = data.redirect || '/';
            //         } else {
            //             // Afficher le message d'erreur
            //             const errorContainer = document.createElement('div');
            //             errorContainer.className = 'error-container';
                        
            //             const errorMessage = document.createElement('p');
            //             errorMessage.className = 'error';
            //             errorMessage.textContent = data.message;
                        
            //             errorContainer.appendChild(errorMessage);
                        
            //             // Insérer le message d'erreur au début du formulaire
            //             const firstChild = loginForm.firstChild;
            //             loginForm.insertBefore(errorContainer, firstChild);
                        
            //             // Si des erreurs spécifiques sont présentes, les afficher
            //             if (data.data && data.data.errors) {
            //                 const errors = data.data.errors;
                            
            //                 for (const field in errors) {
            //                     const errorElement = document.getElementById(field).nextElementSibling;
            //                     if (errorElement) {
            //                         errorElement.textContent = errors[field];
            //                     }
            //                 }
            //             }
            //         }
            //     })
            //     .catch(error => {
            //         console.error('Erreur lors de la connexion:', error);
                    
            //         // // Afficher un message d'erreur générique
            //         // const errorContainer = document.createElement('div');
            //         // errorContainer.className = 'error-container';
                    
            //         // const errorMessage = document.createElement('p');
            //         // errorMessage.className = 'error';
            //         // errorMessage.textContent = 'Une erreur est survenue lors de la connexion. Veuillez réessayer.';
                    
            //         // errorContainer.appendChild(errorMessage);
                    
            //         // // Insérer le message d'erreur au début du formulaire
            //         // const firstChild = loginForm.firstChild;
            //         // loginForm.insertBefore(errorContainer, firstChild);
            //     });
            // }
        });
    }
    
    // Fonction pour valider une adresse email
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});
