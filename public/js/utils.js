   // public/js/utils.js
   function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  }

  function getTypeClass(type) {
    switch (type) {
      case 'roman': return 'bg-primary';
      case 'manhwa': return 'bg-success';
      case 'anime': return 'bg-danger';
      default: return 'bg-secondary';
    }
  }

  module.exports = { formatDate, getTypeClass };