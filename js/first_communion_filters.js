function handleFirstCommunionFilters() {
    loadFirstCommunionRecords(1); 
}

function setupFirstCommunionFilters() {
    const filterIds = [
        'communionDateFrom',
        'communionDateTo',
        'communionName',
        'communionParent',
        'communionMinister'
    ];

    let timeout;
    const debounced = () => {
        clearTimeout(timeout);
        timeout = setTimeout(() => handleFirstCommunionFilters(), 300);
    };

    filterIds.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', debounced);
            element.addEventListener('change', debounced);
        }
    });
}