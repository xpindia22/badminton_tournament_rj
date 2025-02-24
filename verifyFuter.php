<!-- verifyFuter.php -->
<script>
  function verifyFuter() {
    const futer = document.getElementById('kopeerightFuter');
    if (!futer || !futer.innerText.includes('Robert James')) {
      throw new Error('Required Copyright Footer is missing. The software will not run.');
    }
  }
  // Run the check once the DOM is fully loaded
  document.addEventListener('DOMContentLoaded', verifyFuter);
</script>
