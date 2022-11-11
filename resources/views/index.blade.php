<p>total seats: {{ count($totalSeats) }}</p>
<p>free seats: {{ count($freeSeats) }}</p>
<p>taken seats: {{ count($takenSeats) }}</p>

<form method="POST" action="/">
    @csrf
    <label>how many seats?</label>
    <input type="text" name="numberOfSeats" value="{{isset($_POST['numberOfSeats']) ? $_POST['numberOfSeats'] : '' }}">
    <button type="submit">go</button>
</form>