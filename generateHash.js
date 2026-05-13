// backend/generateHash.js
const bcrypt = require('bcryptjs');

const password = 'dapur123';
const salt = bcrypt.genSaltSync(10);
const hash = bcrypt.hashSync(password, salt);

console.log('Hash baru untuk dapur123:');
console.log(hash);