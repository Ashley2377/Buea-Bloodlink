// This file contains functions for making API calls using the fetch API, such as fetching blood requests and user registration.

const API_BASE_URL = 'php/api.php';

async function fetchBloodRequests() {
    try {
        const response = await fetch(`${API_BASE_URL}?action=blood-requests`);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error fetching blood requests:', error);
        throw error;
    }
}

async function registerUser(userData) {
    try {
        const response = await fetch(`${API_BASE_URL}?action=register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(userData),
        });
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error registering user:', error);
        throw error;
    }
}

async function requestBloodUnits(requestData) {
    try {
        const response = await fetch(`${API_BASE_URL}?action=request-blood`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData),
        });
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error requesting blood units:', error);
        throw error;
    }
}