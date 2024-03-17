package org.example;

import com.sun.net.httpserver.HttpServer;

import java.io.IOException;
import java.net.InetSocketAddress;

public class Main {
    public static void main(String[] args) throws IOException {
        HttpServer server = HttpServer.create(new InetSocketAddress(8000), 0);
        server.createContext("/poker", new PokerHandler());
        server.setExecutor(null);
        server.start();

        System.out.println("Server started on port 8000");
    }
}