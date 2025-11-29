program LegacyCSV;

{$mode objfpc}{$H+}

uses
  SysUtils, DateUtils, Unix;

function GetEnvDef(const name, def: string): string;
var
  v: string;
begin
  v := GetEnvironmentVariable(name);
  if v = '' then
    Exit(def)
  else
    Exit(v);
end;

function RandFloat(minV, maxV: Double): Double;
begin
  Result := minV + Random * (maxV - minV);
end;

function CsvTimestamp(const dt: TDateTime): string;
begin
  Result := FormatDateTime('yyyy-mm-dd hh:nn:ss', dt);
end;

function CsvNumber(const v: Double): string;
var
  fs: TFormatSettings;
begin
  fs := DefaultFormatSettings;
  fs.DecimalSeparator := '.';
  Result := FormatFloat('0.00', v, fs);
end;

function CsvText(const s: string): string;
begin

  Result := '"' + StringReplace(s, '"', '""', [rfReplaceAll]) + '"';
end;

function CsvBool(const b: Boolean): string;
begin
  // Для BOOLEAN в Postgres: TRUE/FALSE
  if b then
    Result := 'TRUE'
  else
    Result := 'FALSE';
end;

procedure GenerateAndCopy;
var
  outDir, fn, fullpath, xlsxPath: string;
  pghost, pgport, pguser, pgpass, pgdb, copyCmd: string;
  f: TextFile;
  tsFile: string;
  nowTs: TDateTime;
  voltage, temp: Double;
  isNominal: Boolean;
begin
  outDir := GetEnvDef('CSV_OUT_DIR', '/data/csv');
  nowTs  := Now;
  tsFile := FormatDateTime('yyyymmdd_hhnnss', nowTs);
  fn     := 'telemetry_' + tsFile + '.csv';
  fullpath := IncludeTrailingPathDelimiter(outDir) + fn;

  // --- Генерация CSV ---
  AssignFile(f, fullpath);
  Rewrite(f);

  // Заголовок с логическим полем is_nominal
  Writeln(f, 'recorded_at,voltage,temp,source_file,is_nominal');

  voltage   := RandFloat(3.2, 12.6);
  temp      := RandFloat(-50.0, 80.0);

  isNominal := (voltage >= 5.0);

  Writeln(f,
    CsvTimestamp(nowTs) + ',' +
    CsvNumber(voltage)  + ',' +
    CsvNumber(temp)     + ',' +
    CsvText(fn)         + ',' +
    CsvBool(isNominal)
  );

  CloseFile(f);
  // --- Конвертация в XLSX с помощью LibreOffice
  xlsxPath := StringReplace(fullpath, '.csv', '.xlsx', []);

  fpSystem(
    'libreoffice --headless --convert-to xlsx "' +
    fullpath +
    '" --outdir "' +
    outDir +
    '"'
  );

  // COPY в Postgres из CSV
  pghost := GetEnvDef('PGHOST', 'db');
  pgport := GetEnvDef('PGPORT', '5432');
  pguser := GetEnvDef('PGUSER', 'monouser');
  pgpass := GetEnvDef('PGPASSWORD', 'monopass');
  pgdb   := GetEnvDef('PGDATABASE', 'monolith');

  copyCmd :=
    'psql "host=' + pghost +
    ' port=' + pgport +
    ' user=' + pguser +
    ' dbname=' + pgdb + '" ' +
    '-c "\copy telemetry_legacy(recorded_at, voltage, temp, source_file, is_nominal) ' +
    'FROM ''' + fullpath + ''' WITH (FORMAT csv, HEADER true)"';

  // PGPASSWORD 
  fpSystem('PGPASSWORD=' + pgpass + ' ' + copyCmd);
end;

var
  period: Integer;
begin
  Randomize;
  period := StrToIntDef(GetEnvDef('GEN_PERIOD_SEC', '300'), 300);
  while True do
  begin
    try
      GenerateAndCopy();
    except
      on E: Exception do
        WriteLn('Legacy error: ', E.Message);
    end;
    Sleep(period * 1000);
  end;
end.
