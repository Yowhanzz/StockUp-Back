// Backend API URL
const API_URL = 'http://localhost/STOCKUP/Backend/public/Inventory';

// Function to fetch and display all inventory items
async function fetchInventory() {
  try {
    const response = await fetch(`${API_URL}/getAllItems`, {
      method: 'GET',
      credentials: 'include', // Include cookies for auth
    });
    const data = await response.json();
    const inventoryList = document.getElementById('inventory-list');

    if (data.status === 'success') {
      inventoryList.innerHTML = data.payload.map(item => `
        <div>
          <p>Item: ${item.item_name}</p>
          <p>Brand: ${item.brand}</p>
          <p>Category: ${item.category}</p>
          <p>Quantity: ${item.quantity}</p>
        </div>
      `).join('');
    } else {
      inventoryList.innerHTML = `<p>Error fetching items: ${data.message}</p>`;
    }
  } catch (error) {
    console.error('Error fetching inventory:', error);
  }
}

// Function to add a new item
async function addItem(event) {
  event.preventDefault();
  const itemName = document.getElementById('item_name').value;
  const brand = document.getElementById('brand').value;
  const category = document.getElementById('category').value;
  const quantity = parseInt(document.getElementById('quantity').value);

  try {
    const response = await fetch(`${API_URL}/addItem`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ item_name: itemName, brand, category, quantity }),
    });
    const data = await response.json();
    if (data.status === 'success') {
      alert('Item added successfully');
      fetchInventory(); // Refresh the list
    } else {
      alert(`Error: ${data.message}`);
    }
  } catch (error) {
    console.error('Error adding item:', error);
  }
}

// Attach event listeners
document.getElementById('add-item-form').addEventListener('submit', addItem);

// Fetch inventory on page load
fetchInventory();

// Function to search for an item by name
async function searchItem(event) {
  event.preventDefault();
  const itemName = document.getElementById('search-item-name').value;

  if (!itemName.trim()) {
    alert('Please enter an item name.');
    return;
  }

  try {
    const response = await fetch(`${API_URL}/getItemByName`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ item_name: itemName }),
    });

    const data = await response.json();
    const searchResult = document.getElementById('search-result');

    if (data.status === 'success' && data.payload) {
      const item = data.payload;
      searchResult.innerHTML = `
        <div>
          <h3>Search Result:</h3>
          <p><strong>Item:</strong> ${item.item_name}</p>
          <p><strong>Brand:</strong> ${item.brand}</p>
          <p><strong>Category:</strong> ${item.category}</p>
          <p><strong>Quantity:</strong> ${item.quantity}</p>
        </div>
      `;
    } else {
      searchResult.innerHTML = `<p>No item found with the name "${itemName}".</p>`;
    }
  } catch (error) {
    console.error('Error searching for item:', error);
  }
}

// Attach event listener to the search form
document.getElementById('search-item-form').addEventListener('submit', searchItem);
