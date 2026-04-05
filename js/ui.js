// ui.js
const ui = {
    initDashboard: function() {
        this.bindEvents();
        this.loadUserInfo();
    },

    bindEvents: function() {
        document.getElementById('search-blood').addEventListener('change', this.updateSearchResults);
        document.getElementById('search-loc').addEventListener('input', this.updateSearchResults);
    },

    loadUserInfo: function() {
        // Simulate fetching user info
        const userInfo = { name: "John Doe", bloodType: "A+" };
        document.getElementById('user-info').innerText = `Welcome, ${userInfo.name} (${userInfo.bloodType})`;
    },

    updateSearchResults: function() {
        const bloodType = document.getElementById('search-blood').value;
        const location = document.getElementById('search-loc').value;

        // Simulate fetching search results
        const results = this.fetchBloodDonors(bloodType, location);
        const resultsContainer = document.getElementById('search-results');
        resultsContainer.innerHTML = results.map(donor => `<div>${donor.name} - ${donor.contact}</div>`).join('');
    },

    fetchBloodDonors: function(bloodType, location) {
        // This function would normally call an API to get donor data
        return [
            { name: "Alice Smith", contact: "123-456-7890" },
            { name: "Bob Johnson", contact: "987-654-3210" }
        ];
    },

    book: function() {
        const date = document.getElementById('book-date').value;
        if (date) {
            alert(`Appointment booked for ${date}`);
            // Here you would typically send the booking to the backend
        } else {
            alert('Please select a date for the appointment.');
        }
    },

    request: function() {
        const units = document.getElementById('req-units').value;
        if (units) {
            alert(`Request submitted for ${units} units of blood.`);
            // Here you would typically send the request to the backend
        } else {
            alert('Please specify the number of units required.');
        }
    }
};