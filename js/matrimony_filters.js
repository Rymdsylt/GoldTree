function handleMatrimonyFilters() {
    loadMatrimonyRecords(1); 
}

function setupMatrimonyFilters() {
    const filterIds = [
        'matrimonyBrideName',
        'matrimonyGroomName',
        'matrimonyMinister',
        'matrimonyDateFrom',
        'matrimonyDateTo'
    ];

    let timeout;
    const debounced = () => {
        clearTimeout(timeout);
        timeout = setTimeout(() => handleMatrimonyFilters(), 300);
    };

    filterIds.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', debounced);
            element.addEventListener('change', debounced);
        }
    });
}