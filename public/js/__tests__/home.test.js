   // public/js/__tests__/home.test.js
   const { formatDate, getTypeClass } = require('../utils.js');

   test('formatDate retourne une date formatée', () => {
     expect(formatDate('2023-08-01T12:00:00Z')).toMatch(/2023/);
   });

   test('getTypeClass retourne bg-primary pour roman', () => {
     expect(getTypeClass('roman')).toBe('bg-primary');
   });