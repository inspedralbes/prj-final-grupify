const express = require('express');
const app = express();

app.get('/', (req, res) => {
  res.send('Hello from Node.js service!');
});

const PORT = 5000;
app.listen(PORT, () => {
  console.log(`Node.js server running at http://localhost:${PORT}`);
});
