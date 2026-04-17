# Aer Consulting

Aer Consulting è un gestionale Laravel per laboratori di analisi progettato per gestire il ciclo di vita dei campioni in modo strutturato, sicuro e manutenibile.

Il sistema combina:
- regole di dominio rigorose (soprattutto sui campioni sensibili)
- architettura modulare (Query / Action / ViewModel)
- protezioni multilivello (policy, query, UI masking)

---

## Panoramica Funzionale

### Gestione Campioni

Ogni campione rappresenta un'analisi e segue un workflow definito:

`collected` -> `accepted` -> `completed`

Per ogni campione vengono gestiti:
- codice identificativo
- tipo di campione
- cliente (se presente)
- stato del workflow
- date operative

---

### Tipologie di Campione

I campioni sono classificati tramite `sample_types`.

Ogni tipo può essere marcato come:
- `is_sensitive = true`

Questa proprietà modifica radicalmente il comportamento del sistema.

---

## Campioni Sensibili

### Regola di dominio

Se un campione è sensibile:
- lo staff può solo creare una **preregistrazione tecnica anonima**
- il sistema forza:
  - `client_id = null`
  - `notes = null`

---

### Restrizioni per lo Staff

Lo staff NON può:
- vedere il dettaglio
- modificare il campione
- gestire file
- avanzare il workflow

Può:
- vedere il record in lista
- cercare per codice

---

### Mascheramento Dati

I dati sensibili vengono mascherati nelle liste. Questo include:
- nome cliente
- tipo campione
- informazioni di raccolta

---

### Accesso Admin

L'admin può:
- vedere tutti i dati
- completare il campione
- assegnare il cliente
- gestire file e workflow

---

## Ricerca Sicura

Il sistema impedisce leak di dati sensibili:
- lo staff NON può cercare campioni sensibili tramite cliente
- può trovarli solo tramite codice

Questa logica è implementata nel layer Query.

---

## Archiviazione

I campioni possono essere:
- archiviati
- ripristinati

L'operazione è transazionale e coinvolge anche i file associati.

---

## File Associati

Ogni campione può avere file allegati.

Regole:
- accesso completo per admin
- accesso bloccato per staff su campioni sensibili

---

## Ruoli

### Staff
- inserimento campioni
- consultazione limitata

### Admin
- accesso completo
- gestione workflow
- gestione dati sensibili

---

# Architettura

Il progetto è strutturato per separare chiaramente le responsabilità.

---

## Livello di Lettura (Query)

Namespace:
`App\Queries\`

Responsabilità:
- costruzione query Eloquent
- filtraggio
- sicurezza della ricerca

Esempi:
- `ActiveSamplesIndexQuery`
- `ArchivedSamplesIndexQuery`
- `AppliesStaffSafeSearch`
- `Dashboard*Query`

---

## Livello di Scrittura (Action)

Namespace:
`App\Actions\`

Responsabilità:
- creazione e aggiornamento
- transizioni di stato
- operazioni atomiche (archiviazione/ripristino)

Esempi:
- `CreateSampleAction`
- `UpdateSampleAction`
- `AcceptSampleAction`
- `CompleteSampleAction`
- `ArchiveSampleAction`
- `RestoreSampleAction`
- `GenerateSampleCode`

Le Action:
- NON fanno autorizzazione
- NON fanno query di lettura
- applicano solo regole di dominio e mutazioni

---

## Livello di Presentazione (ViewModel)

Namespace:
`App\ViewModels\`

Esempio principale:
- `SampleRowViewModel`

Responsabilità:
- formattazione dati per le view
- gestione del masking
- eliminazione logica condizionale nelle Blade

Nota:
- il ViewModel è puramente presentazionale
- non contiene logica di dominio o autorizzazione

---

## Controllori

Responsabilità limitate a:
- autorizzazione (`authorize`)
- delega alle Action / Query
- ritorno response

Esempio:
```php
$this->authorize('accept', $sample);
$action->execute($sample, Auth::id());
```

---

## Modello Dati

Il modello Sample contiene:
- regole di stato:
  - `canBeAccepted()`
  - `canBeCompleted()`
- logica di dominio:
  - `isSensitiveIncomplete()`

---

## Flussi di Lavoro

Stati:

`collected` -> `accepted` -> `completed`

Regole:
- non è possibile avanzare campioni sensibili incompleti
- le transizioni sono validate tramite:
  - metodi del model
  - action

---

## Sicurezza

Il sistema usa più livelli:
- Policy (autorizzazione)
- Query (protezione dati)
- Model (regole dominio)
- ViewModel (masking UI)

---

## Test Automatici

Copertura test su:
- creazione campioni sensibili
- override lato server
- sicurezza della ricerca
- policy accesso
- workflow
- gestione file

Tutti i test devono restare invariati durante i refactor.

---

## Configurazione e Avvio

```bash
git clone <repo>
cd aerconsulting
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

---

## Note Architetturali

- niente logica nei controller
- niente query nelle Blade
- niente autorizzazione nelle Action
- separazione chiara tra Query / Action / ViewModel

---

## Debito Tecnico Residuo

- dual-write sample_type (da eliminare)
- proxy ViewModel (`__get`) da rivalutare in futuro
- ottimizzazione query dashboard
