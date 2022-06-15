const thousandFormat = (amount = 0) => {
    return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};
