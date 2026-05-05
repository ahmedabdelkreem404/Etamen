class PaginatedResponse<T> {
  const PaginatedResponse({
    required this.items,
    required this.page,
    required this.perPage,
    required this.hasMore,
  });

  final List<T> items;
  final int page;
  final int perPage;
  final bool hasMore;
}
