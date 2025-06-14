<footer>
    <div class="footer clearfix mb-0 text-muted">
        <div class="float-start">
            <p>{{ date('Y') }} &copy; Generator by
                <a href="https://github.com/Evdigi-INA" target="_blank">Evdigi-INA</a> &
               <a href="https://github.com/Zzzul" target="_blank">Mohammad Zulfahmi</a>
            </p>
        </div>
        <div class="float-end">
            <p>Mazer Template by
                <a href="https://github.com/zuramai/mazer" target="_blank">Ahmad Saugi</a>
            </p>
        </div>
    </div>
</footer>
</div>
    <script src="{{ asset(path: 'mazer') }}/static/js/components/dark.js"></script>
    <script src="{{ asset(path: 'mazer') }}/extensions/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="{{ asset(path: 'mazer') }}/compiled/js/app.js"></script>
    @stack('js')
</body>

</html>
