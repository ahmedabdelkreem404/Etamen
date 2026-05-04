import 'package:connectycube_sdk/connectycube_sdk.dart';

const String APP_ID = String.fromEnvironment('CONNECTYCUBE_APP_ID', defaultValue: '');
const String AUTH_KEY = String.fromEnvironment('CONNECTYCUBE_AUTH_KEY', defaultValue: '');
const String AUTH_SECRET = String.fromEnvironment('CONNECTYCUBE_AUTH_SECRET', defaultValue: '');
const String ACCOUNT_ID = String.fromEnvironment('CONNECTYCUBE_ACCOUNT_ID', defaultValue: '');
const String DEFAULT_PASS = String.fromEnvironment('CONNECTYCUBE_DEFAULT_PASS', defaultValue: '');

List<CubeUser> users = [
  CubeUser(
    id: 5752757,
    login: "call_user_1",
    fullName: "User 1",
    password: DEFAULT_PASS,
  ),
  CubeUser(
    id: 5752758,
    login: "call_user_2",
    fullName: "User 2",
    password: DEFAULT_PASS,
  ),
  CubeUser(
    id: 5726616,
    login: "9988776666642590119#1",
    fullName: "Pe1",
    password: DEFAULT_PASS,
  ),
  CubeUser(
    id: 5726584,
    login: "99887766551561758766#2",
    fullName: "Sunny",
    password: DEFAULT_PASS,
  ),
];
