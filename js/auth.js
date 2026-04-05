// auth.js
const auth = {
    login: async function(email, password) {
        try {
            const response = await fetch('php/api.php?action=login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });

            if (!response.ok) {
                throw new Error('Login failed');
            }

            const data = await response.json();
            this.setSession(data);
            return data;
        } catch (error) {
            console.error('Error during login:', error);
            throw error;
        }
    },

    register: async function(name, email, password, role='donor') {
        try {
            const response = await fetch('php/api.php?action=register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name, email, password, role })
            });

            if (!response.ok) {
                throw new Error('Registration failed');
            }

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error during registration:', error);
            throw error;
        }
    },

    logout: function() {
        // Clear user session
        localStorage.removeItem('user');
        window.location.reload();
    },

    setSession: function(user) {
        localStorage.setItem('user', JSON.stringify(user));
    },

    getCurrentUser: function() {
        return JSON.parse(localStorage.getItem('user'));
    }
};