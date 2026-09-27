    <div class="flex items-center px-4 pb-4 mb-5 text-center border-b border-[var(--border)]">

        <a href="{{ route('admin.season-imports.index') }}">
        <div class="w-52 mx-4 border rounded-md border-[var(--border)] bg-[var(--btn-bg)] hover:bg-[var(--btn-hover)] text-[var(--btn-text)]">
            Season imports
        </div>
        </a>

        <a href="{{ route('admin.season-pdfs.index') }}">
        <div class="w-52 mx-4 border rounded-md border-[var(--border)] bg-[var(--btn-bg)] hover:bg-[var(--btn-hover)] text-[var(--btn-text)]">
            Season PDF
        </div>
        </a>

        <a href="{{ route('admin.season-parser.create') }}">
        <div class="w-52 mx-4 border rounded-md border-[var(--border)] bg-[var(--btn-bg)] hover:bg-[var(--btn-hover)] text-[var(--btn-text)]">
            Season parser
        </div>
        </a>

    </div>
