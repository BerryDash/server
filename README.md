# Berry Dash Server

> [!CAUTION]
> This project has moved to the Lncvrt Games originization.
> You can find the new repository [here](https://github.com/Lncvrt-Games/servers)

---

This is the new rewritten server source code for Berry Dash.

## Setup

> [!CAUTION]
> Do not share any keys with anybody, and store them safely on your computer.
> Encryption keys are meant to increase the security of the game and prevent cheaters.

1. Upload the files on a webserver
2. Import database.sql into a MySQL/MariaDB database
3. Configure everything in `config/` folder (use `python3 genkeys.py` for `encryption.php`)
4. Use `git clone https://github.com/BerryDash/source.git` to clone the client and configure `Assets/Scripts/SensitiveInfo.cs`

**Note:**

When configuring keys in the client, `SERVER_RECEIVE_TRANSFER_KEY` will the key `SERVER_SEND_TRANSFER_KEY` from the server, and the other way around too.

If this isn't configured properly, you will not be able to make requests to the server you have setup.

Example:

```cs
public static readonly string SERVER_DATABASE_PREFIX = "https://berrydash.lncvrt.xyz/database/";
public static readonly string SERVER_RECEIVE_TRANSFER_KEY = "02b3c624552588ba0929e43fbad36221"; // This would be `SERVER_SEND_TRANSFER_KEY` from the server
public static readonly string SERVER_SEND_TRANSFER_KEY = "d4269db023de05fd59bf05378fa57324"; // This would be `SERVER_RECEIVE_TRANSFER_KEY` from the server
public static readonly string BAZOOKA_MANAGER_KEY = "19387a0e6b861e79d38657d0059bce8c";
public static readonly string BAZOOKA_MANAGER_FILE_KEY = "7fb47db04d9babf42ee3713289516208";
```

In conclusion: `SERVER_RECEIVE_TRANSFER_KEY` and `SERVER_SEND_TRANSFER_KEY` are swapped between client and server. This may be changed in the future.
